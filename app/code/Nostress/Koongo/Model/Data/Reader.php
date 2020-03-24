<?php
/**
 * Magento Module developed by NoStress Commerce
 *
 * NOTICE OF LICENSE
 *
 * This program is licensed under the Koongo software licence (by NoStress Commerce). 
 * With the purchase, download of the software or the installation of the software 
 * in your application you accept the licence agreement. The allowed usage is outlined in the
 * Koongo software licence which can be found under https://docs.koongo.com/display/koongo/License+Conditions
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at https://store.koongo.com/.
 *
 * See the Koongo software licence agreement for more details.
 * @copyright Copyright (c) 2017 NoStress Commerce (http://www.nostresscommerce.cz, http://www.koongo.com/)
 *
 */

/**
* File reader
* @category Nostress
* @package Nostress_Koongo
*/
namespace Nostress\Koongo\Model\Data;

class Reader extends \Nostress\Koongo\Model\AbstractModel
{
	const SOURCE_ENCODING = "src_encoding";
	const DESTINATION_ENCODING = "dst_encoding";
	const SKIP_FIRST = "skip_first_record";
	const TMP_FILES_DIR = '_nsc_koongo_temp_dir';
	const URL_PATH_DELIMITER = "/";
	
	protected $_reader;
	protected $_fileType;
	protected $_filePath = "";
	protected $_tmpFilePath = "";
	protected $_params;
	protected $_dstEncoding = 'utf-8';
	protected $_srcEncoding = null;
	protected $_skipFirst = 0;
	
	protected $_readerCsv;
	protected $_readerTxt;
	protected $_readerDefault;
	
	/**
	 * Construct
	 * @param \Nostress\Koongo\Model\Data\Reader\Common\TextFactory $readerTextFactory
	 * @param \Nostress\Koongo\Model\Data\Reader\Common\CsvFactory $readerCsvFactory
	 * @param \Nostress\Koongo\Model\Data\Reader\CommonFactory $readerDefaultFactory
	 * @param \Nostress\Koongo\Helper\Data
	 */
	public function __construct(\Nostress\Koongo\Model\Data\Reader\Common\Text $readerText,
								\Nostress\Koongo\Model\Data\Reader\Common\Csv $readerCsv,
								\Nostress\Koongo\Model\Data\Reader\Common $readerDefault,
								\Nostress\Koongo\Helper\Data $helper
								)
	{
		$this->_readerCsv = $readerCsv;
		$this->_readerText = $readerText;
		$this->_readerDefault = $readerDefault;
		$this->helper = $helper;
	}
	
	public function getRemoteFileContent($url)
	{
		if(empty($url))
			return "";
		$offset = strripos($url, "/") + 1;
		$filename = substr($url,$offset);
		$params = array(self::FILE_PATH => $url,self::FILE_NAME => $filename);
		$this->initSimpleParams($params);
		$this->openFile(array());
		$result = $this->getFileContentAsString();
		$this->closeFile();
		return $result;
	}
	
	public function getTaxonomyFileContent($params)
	{
		$this->initTaxonomyParams($params);
		$this->openFile($params);
		$result = $this->getAllRecords();
		$this->closeFile();
		return $result;
	}
	
	public function openFile($params)
	{
		$result = false;
		switch($this->_fileType)
		{
			case self::TYPE_CSV:
				$this->_reader = $this->_readerCsv;
				break;
			case self::TYPE_TEXT:
				$this->_reader = $this->_readerText;
				break;
			default:
				$this->_reader = $this->_readerDefault;
				break;
		}
		$this->downloadFileToLocalDirectory();
		return $this->_reader->openFile($this->_tmpFilePath,$params);
	}
	
	public function getAllRecords()
	{
		$result = array();
		$record = $this->getRecord();
		
		if($this->_skipFirst)
			$record = $this->getRecord();
			
		while($record != false)
		{
			$result[] = $record;
			$record = $this->getRecord();
		}
		return $result;
	}
	
	public function getFileContentAsString()
	{
		$content = "";
		$record = $this->getRecord();
		while($record != false)
		{
			$content .= $record;
			$record = $this->getRecord();
		}
		return $content;
	}
	
	protected function getTemporaryFilesDirectory()
	{
		return $this->helper->getFeedStorageDirPath(null,self::TMP_FILES_DIR);
	}
	
	protected function initSimpleParams($params)
	{
		$this->initParam($this->_filePath,$params[self::FILE_PATH]);
		$this->initParam($this->_tmpFilePath,$this->getTemporaryFilesDirectory().$params[self::FILE_NAME]);
	}
	
	protected function initTaxonomyParams($params)
	{
		$this->initParam($this->_dstEncoding,$params[self::DESTINATION_ENCODING]);
		$this->initParam($this->_srcEncoding,$params[self::SOURCE_ENCODING]);
		$this->initParam($this->_fileType,$params[self::FILE_TYPE]);
		$path = $params[self::FILE_PATH];
		$this->initParam($this->_filePath,$path.$params[self::FILE_NAME]);
		$this->initParam($this->_tmpFilePath,$this->getTemporaryFilesDirectory().$params[self::FILE_NAME]);
		$this->initParam($this->_skipFirst,$params[self::SKIP_FIRST]);
	}
	
	protected function initParam(&$param,$value)
	{
		if(isset($value) && !empty($value))
			$param = $value;
	}
	
	public function getRecord()
	{
		if(isset($this->_reader))
			return $this->helper->changeEncoding($this->_dstEncoding,$this->_reader->getRecord(),$this->_srcEncoding) ;
		else
			return false;
	}
	
	public function getRecordNoEncodingChange()
	{
		return $this->_reader->getRecord();
	}
	
	protected function closeFile()
	{
		if(isset($this->_reader))
		{
		    $result = $this->_reader->closeFile();
		    $this->helper->deleteFile($this->_tmpFilePath);
		    return $result;
		}
		else
			return false;
	}
	
    protected function downloadFileToLocalDirectory()
    {
        $this->helper->createDirectory($this->getTemporaryFilesDirectory());
        $this->downloadFile($this->_filePath,$this->_tmpFilePath);
    }
	
    public function downloadFile($fileUrl, $localFilename)
    {
    	$err_msg = '';
    
    	$out = fopen($localFilename,"wb");
    	if (!$out)
    	{
    		$message = __("Can't open file %1 for writing", $localFilename);
    		throw new \Exception($message);
    	}
    
    	$ch = curl_init();
    
    	curl_setopt($ch, CURLOPT_FILE, $out);
    	curl_setopt($ch, CURLOPT_HEADER, 0);
    	curl_setopt($ch, CURLOPT_URL, $fileUrl);
    	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    
    	curl_exec($ch);
    
    	/* Check for 404 (file not found). */
    	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    	if($httpCode == 404)
    	{
    		/* Handle 404 here. */
    		$message = __("File %1 doesn't exist. The file url location returns error 404.",$fileUrl);
    		throw new \Exception($message);
    	}
    
    	$error = curl_error ( $ch);
    	if(!empty($error))
    	{
    		$message = __("Can't download file %1 Following error occurs: %2",$fileUrl,$error);
    		throw new \Exception($message);
    	}
    
    	curl_close($ch);
    	fclose($out);
    
    }//end function
    
    public function downloadFileToString($fileUrl)
    {
    	$err_msg = '';
    		
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_HEADER, 0);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_URL, $fileUrl);
    	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    	
    	$data = curl_exec($ch);
    
    	/* Check for 404 (file not found). */
    	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    	if($httpCode == 404)
    	{
    		/* Handle 404 here. */
    		$message = __("File %1 doesn't exist. The file url location returns error 404.",$fileUrl);
    		Mage::throwException($message);
    	}
    
    	$error = curl_error ( $ch);
    	if(!empty($error))
    	{
    		$message = __("Can't download file %1",$fileUrl);
    		throw new \Exception($message);
    	}
    
    	curl_close($ch);
    	fclose($out);
    
    	return $data;
    }//end function
}
?>