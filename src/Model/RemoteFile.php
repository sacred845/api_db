<?php

namespace App\Model;

use App\Entity\UpdateFile;
use App\Model\Core;
use App\Model\Logger;

class RemoteFile
{
	private $url;
	private $originname;
	private $tmpfilename;
	private $tmpfilepath;
	private $archivefilename;
	private $archivefilepath;
	private $tmpdir;
	private $logger;
	private $unpuckfilepath;
	
	public function __construct ($url)
	{
		$this->url = $url;
	}
	
	public function __destruct()
	{
		if ($this->tmpdir) {
			$path = (Core::getInstance())->getTmpPath() . $this->tmpdir;
			exec('rm -rf '.$path);			
		}
	}
	
	public function getOriginName(): ?string
	{
		return $this->originname;
	}
	
	public function getArchiveFileName(): string
	{
		return $this->archivefilename;
	}
	
	public function getUnpuckfilepath(): string
	{
		return $this->unpuckfilepath;
	}
	
	public function getArchiveFilePath(): string
	{
		return $this->archivefilepath;
	}	
	
	protected function getProxy(): ?string
	{
		return (Core::getInstance())->getRandomProxy();
	}	
	
	public function getOriginSize()
	{
		return ceil(filesize($this->tmpfilepath)/1024);
	}
	
	public function getArchiveSize()
	{
		return ceil(filesize($this->archivefilepath)/1024);
	}
	
	public function getHash(): ?string
	{
		return hash_file('sha256', $this->tmpfilepath);
	}
	
	protected function isAccessbuleQuery($code)
	{
		return !in_array($code, [401,502, 429, 404, null, 0]);
	}		
	
    private function checkHeaders($url, $useproxy)
    {
        $fileName = '';
        $handle = curl_init($url);

		curl_setopt($handle, CURLOPT_HEADER, true);
		curl_setopt($handle, CURLOPT_NOBODY, true);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_FOLLOWLOCATION, 1); 
		curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($handle, CURLOPT_HTTPHEADER, (Core::getInstance())->getRandomUserAgent());
		
		$i = 0;
		$num = 1;
		$httpCode = null;
		if ($useproxy)
			$num = 100;		
		
		while (!$this->isAccessbuleQuery($httpCode) && ($i < $num)) {
			if ($useproxy) {
				$proxy = $this->getProxy();
				$proxyitems = explode(':', $proxy);
				if (count($proxyitems) == 4) {
					curl_setopt($handle, CURLOPT_PROXY, $proxyitems[0].':'.$proxyitems[1]);
					curl_setopt($handle, CURLOPT_PROXYUSERPWD, $proxyitems[2].':'.$proxyitems[3]);
				} else
					curl_setopt($handle, CURLOPT_PROXY, $proxy);
				curl_setopt($handle, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			}
			$response = curl_exec($handle);
			$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
			$lasturl = curl_getinfo($handle, CURLINFO_EFFECTIVE_URL);
			$info = curl_getinfo($handle);
			$i++;			
		}
		curl_close($handle);
		if ($useproxy)
			$this->log(Logger::PRIORITY_INFO, 'Используется прокси - '.$proxy);
		
		$this->log(Logger::PRIORITY_DEBUG, 'Return code '.$httpCode);
        if ($this->isAccessbuleQuery($httpCode)) {
			$headers = get_headers($info['url'] ?? $url);

            foreach ($headers as $item) {
                $pos = strpos(strtolower($item), 'content-disposition');
                if ($pos !== false) {

                    $fileNameArr = explode('=', str_replace(' ', '', $item));
                    $fileName = str_replace('"', '', $fileNameArr[count($fileNameArr) - 1]);
					$fileName = str_replace(';', '', $fileName);	
                }
            }

            if ($fileName != '') {
                return $fileName;
            }

			preg_match('~[^/]+\..{1,5}$~', $url, $res);

			if ($res[0] ?? null)
				return $res[0];

            $reDispo = '/Content-Disposition: attachment; filename=".*"/ui';

            $headers = implode(' , ', $headers);

            if (preg_match('~filename=(?|"([^"]*)"|\'([^\']*)\'|([^;]*))~', $headers, $match)) {
                return $match[1];
            }
            if (preg_match('~filename=(.*) ~', $headers, $match)) {
                return $match[1];
            } else {
                $fileName = basename($lasturl);

                if ($fileName != '') {
                    return $fileName;
                } else {
                    return 'unable-to-get-filename';
                }
            }
        } else {
            return false;
        }

    }	
	
	private function getTypeArchive(): ?string
	{
		$fileinfo = pathinfo($this->tmpfilepath);
		if ($fileinfo['extension'] ?? null) {
			if (in_array($fileinfo['extension'], ['xz', 'gz', 'bz2', 'zip', 'rar'])) {
				switch ($fileinfo['extension']) {
					case 'xz' :
					case 'gz' :
					case 'bz2' :
						if (strpos($fileinfo['extension'], '.tar'))
							return 'tar.'.$fileinfo['extension'];
						else
							return $fileinfo['extension'];
					case 'rar' :
						return 'rar';
						
					case 'zip' :
						return 'zip';
				}
			}
		}
		return null;
	}
	
	public function unpuckFile(): self
	{
		$path = (Core::getInstance())->getTmpPath() . $this->tmpdir;
		chdir($path);

		if ($typearh = $this->getTypeArchive()) {
			$this->log(Logger::PRIORITY_INFO, 'Work with archive \''.$typearh.'\'');
			$foldername = $this->tmpfilename.'_tmp';
			$folderpath = $path.$foldername;
			$this->log(Logger::PRIORITY_DEBUG, 'Create catalog to unpuck');
			mkdir($folderpath, 0777);
			$this->log(Logger::PRIORITY_INFO, 'Unpuck files');	
			switch ($typearh) {
				case 'zip' :
				exec('unzip "'.$this->tmpfilepath.'" -d '.$folderpath. ' > /dev/null', $output, $return_var);
				if ($return_var) {
					$this->log(Logger::PRIORITY_ERROR, 'Ошибка распакови файла');
					throw new \Exception('Не удается упаковать файлы в \''.$foldername.'\'');
				}	
			}
			$files = scandir($folderpath);
			foreach ($files as $file) {
				if (($file != '.' ) && ($file != '..'))
					$this->unpuckfilepath = $folderpath.'/'.$file;
			}
		}
		
		
		return $this;
	}
	
	public function packToZip(): self
	{
		$path = (Core::getInstance())->getTmpPath() . $this->tmpdir;
		chdir($path);
		if ($typearh = $this->getTypeArchive()) {
			$this->log(Logger::PRIORITY_INFO, 'Work with archive \''.$typearh.'\'');
			$foldername = $this->tmpfilename.'_tmp';
			$folderpath = $path.$foldername;

			if ($typearh != 'zip') {
				$this->log(Logger::PRIORITY_DEBUG, 'Create catalog to unpuck');
				mkdir($folderpath, 0777);
				$this->log(Logger::PRIORITY_INFO, 'Unpuck files');				
			}
			switch ($typearh) {
				case 'gz' :
					exec('zcat "'.$this->tmpfilepath.'" > '.$folderpath. '/'.substr($this->tmpfilename, 0, strpos($this->tmpfilename, '.gz')), $output, $return_var);
					if ($return_var) {
						$this->log(Logger::PRIORITY_ERROR, 'Error unpuck files');
						exec('rm -rf '.$folderpath);
						throw new \Exception('Не удается распоковать tar-архив \''.$this->tmpfilepath.'\'');
					}
					break;
				case 'tar.bz2' :
					exec('tar -jxf "'.$this->tmpfilepath.'" -C '.$folderpath. ' > /dev/null', $output, $return_var);
					if ($return_var) {
						$this->log(Logger::PRIORITY_ERROR, 'Error unpuck files');
						exec('rm -rf '.$folderpath);
						throw new \Exception('Не удается распоковать tar-архив \''.$this->tmpfilepath.'\'');
					}
					break;	
				case 'tar.gz' :
				case 'tar.xz' :
					exec('tar -zxf "'.$this->tmpfilepath.'" -C '.$folderpath. ' > /dev/null', $output, $return_var);
					if ($return_var) {
						$this->log(Logger::PRIORITY_ERROR, 'Error unpuck files');
						exec('rm -rf '.$folderpath);
						throw new \Exception('Не удается распоковать tar-архив \''.$this->tmpfilepath.'\'');
					}
					break;		
				case 'tar' :
					exec('tar -xf "'.$this->tmpfilepath.'" -C '.$folderpath. ' > /dev/null', $output, $return_var);
					if ($return_var) {
						$this->log(Logger::PRIORITY_ERROR, 'Error unpuck files');
						exec('rm -rf '.$folderpath);
						throw new \Exception('Не удается распоковать tar-архив \''.$this->tmpfilepath.'\'');
					}
					break;
				case 'rar' :
					exec('/usr/local/bin/unrar e "'.$this->tmpfilepath.'" '.$folderpath. ' > /dev/null', $output, $return_var);
					if ($return_var) {
						$this->log(Logger::PRIORITY_ERROR, 'Error unpuck files');
						exec('rm -rf '.$folderpath);
						throw new \Exception('Не удается распоковать rar-архив \''.$this->tmpfilepath.'\'');
					}
					break;
				case 'zip' :
					$this->log(Logger::PRIORITY_DEBUG, 'It\'s zip-archive');
					//$this->archivefilepath = $this->tmpfilepath;
					//$this->archivefilename = $this->tmpfilename;
					$this->archivefilename = md5($this->tmpfilename . time() . rand(1, 10000)) . '.zip';
					$this->archivefilepath = $path . $this->tmpfilename;
					break;
			}
			if ($typearh != 'zip') {
				//$this->archivefilepath = $this->tmpfilepath.'.zip';
				//$this->archivefilename = $this->tmpfilename.'.zip';
				$this->archivefilename = md5($this->tmpfilename . time() . rand(1, 10000)) . '.zip';
				$this->archivefilepath = $path . $this->archivefilename;
				$this->log(Logger::PRIORITY_INFO, 'Puck files to zip');
				exec ('zip '.$this->archivefilepath.' -r '.$foldername.' > /dev/null', $output, $return_var);
				exec('rm -rf '.$folderpath);
				if ($return_var) {
					$this->log(Logger::PRIORITY_ERROR, 'Error puck files');
					throw new \Exception('Не удается упаковать файлы из \''.$foldername.'\'');
				}				
			}

		} else {
			//$this->archivefilepath = $this->tmpfilepath.'.zip';
			//$this->archivefilename = $this->tmpfilename.'.zip';
			
			$this->archivefilename = md5($this->tmpfilename . time() . rand(1, 10000)) . '.zip';
			$this->archivefilepath = $path . $this->archivefilename;	
			$this->log(Logger::PRIORITY_INFO, 'Puck files to zip');
			exec ('zip '.$this->archivefilepath.' "'.$this->tmpfilename.'" > /dev/null', $output, $return_var);
			if ($return_var) {
				$this->log(Logger::PRIORITY_ERROR, 'Error puck files');
				throw new \Exception('Не удается упаковать файлы из \''.$this->tmpfilepath.'\'');
			}		
		}

		return $this;
	}
	
	private function getExtention($filename): ?string
	{
		$fileinfo = pathinfo($this->originname);
		$ext = '';
		if ($fileinfo['extension'] ?? null) {
			if (in_array($fileinfo['extension'], ['gz', 'bz2', 'xz']))
				$ext = 'tar.';
			$ext .= $fileinfo['extension'];
		}
						
		return $ext;
	}
	
	protected function translateUrl(string $url): string
	{
		$indate = [
			'YYYY',
			'YY',
			'MM',
			'M',
			'DD',
			'D'
		];
		
		$outdate = [
			'Y',
			'y',
			'm',
			'n',
			'd',
			'j'
		];	
		preg_match_all('~{{(.+?)}}~', $url, $res);

		if ($res[1] ?? null) {
			foreach ($res[1] as $item) {
				$data = explode('|', $item);

				if (($data[0] ?? null) == 'date') {
					$dt = new \DateTime('now');
					$format = $data[1] ?? 'Y-m-d';
					$format = str_replace($indate, $outdate, $format);
					$utc = $data[2] ?? null;
					if (strpos($utc, 'UTC') === 0) {
						list($utc_h, $utc_m) = explode(':', substr($utc, strlen('UTC')));
						if ($utc_h >= 0) {
							$dt->add(new \DateInterval('PT'.abs($utc_h).'H'.$utc_m.'M'));
						} else {
							$dt->sub(new \DateInterval('PT'.abs($utc_h).'H'.$utc_m.'M'));
						}
					}

					$url = str_replace('{{'.$item.'}}', $dt->format($format), $url);
				}
			}
		}
		return $url;
	}
	
	public function download(): bool
	{
		$path = (Core::getInstance())->getTmpPath();
		$url = $this->url;
		$this->log(Logger::PRIORITY_INFO, 'Start with url \''.$url.'\'');
		$useproxy = false;
        $this->originname = $this->checkHeaders($url, $useproxy);
		
        $tmpFilename = '';
		$this->log(Logger::PRIORITY_DEBUG, 'Original file name is \''.$this->originname.'\'');
        if ($this->originname) {
			$ext = $this->getExtention($this->originname);
            $tmpFilename = $this->originname;
			$this->tmpdir = md5($this->originname . time() . rand(1, 10000)) .'/';
			$path .= $this->tmpdir;
			$this->log(Logger::PRIORITY_DEBUG, 'Create temporary catalog \''.$this->tmpdir.'\'');
			mkdir($path, 0777);
			
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url); 
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
          //  curl_setopt($ch, CURLOPT_REFERER, (Core::getInstance())->getRefererForCurl());
            curl_setopt($ch, CURLOPT_HTTPHEADER, (Core::getInstance())->getRandomUserAgent());
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			
			$i = 0;
			$num = 1;
			$code = null;
			if ($useproxy)
				$num = 100;					
			while (!$this->isAccessbuleQuery($code) && ($i < $num)) {
				$fp = fopen($path . $tmpFilename, 'w+b');
				stream_set_write_buffer ( $fp , 0 );
				curl_setopt($ch, CURLOPT_FILE, $fp);
				if ($useproxy) {
					$proxy = $this->getProxy();
					$proxyitems = explode(':', $proxy);
					if (count($proxyitems) == 4) {
						curl_setopt($ch, CURLOPT_PROXY, $proxyitems[0].':'.$proxyitems[1]);
						curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyitems[2].':'.$proxyitems[3]);
					} else
						curl_setopt($ch, CURLOPT_PROXY, $proxy);
					curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
				}
				curl_exec($ch);
				$info = curl_getinfo($ch);		
		
				$i++; 
				$code = $info['http_code'];
				fclose($fp);
			}

            curl_close($ch);

			if ($useproxy)
				$this->log(Logger::PRIORITY_INFO, 'Используется прокси - '.$proxy);

			if (!$this->isAccessbuleQuery($code)) {
				$this->log(Logger::PRIORITY_ERROR, 'Ошибка загрузки файла');
				return false;
			}			

			// Cherck html-response

				$fp = fopen($path . $tmpFilename, 'r');
				$str = fread($fp, strlen('<!DOCTYPE HTML>'));
				fclose($fp);
				if ((strtolower($str) == strtolower('<!DOCTYPE HTML>')) ||
						(strpos(strtolower($str), strtolower('<html>')) === 0))
				{
					$this->log(Logger::PRIORITY_ERROR, 'Загружена HTML-страница');
					return false;
				}


			$this->tmpfilename = $tmpFilename;
			$this->tmpfilepath = $path . $tmpFilename;
			$this->log(Logger::PRIORITY_INFO, 'Файл загружен успешно');
			return true;
        }
		$this->log(Logger::PRIORITY_ERROR, 'Ошибка загрузки файла');
		return false;
	}
	
    public function log(string $priority, ?string $mes): self
    {
		(Core::getInstance())->toLog($priority, $mes, 'Files_logger');

        return $this;
    }	
}
