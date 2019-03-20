<?php

namespace Markocupic\Famulatur\Hooks;

use Contao\ContentModel;
use Contao\Dbafs;
use Contao\FilesModel;
use Contao\Folder;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;

/**
 * Class FTP2
 * @package Markocupic\Famulatur\Hooks
 */
class FTP2
{

    /**
     * @var
     */
    private $ftp_hostname;

    /**
     * @var
     */
    private $ftp_username;

    /**
     * @var
     */
    private $ftp_password;

    /**
     *
     */
    private $root_dir;

    public function initCurl()
    {
        $handle = curl_init();

        $url = "https://kletterkader.com?bla=sdfsdf";

        curl_setopt($handle, CURLOPT_URL, $url);

        // Set the result output to be a string.
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

        $output = curl_exec($handle);

        curl_close($handle);

        return $output;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function loadDataFromFtp()
    {
        if (!strlen(Input::get('test')))
        {
            return;
        }

        // Get data from remote host
        $strBuffer = $this->initCurl();
        if (false === $strBuffer || '' == $strBuffer)
        {
            return;
        }

        // and store it to $arrMultiSRC, $arrOrderSRC, $arrFilePath
        $objCurl = StringUtil::deserialize(\GuzzleHttp\json_decode($strBuffer), true);
        $arrMultiSRC = $objCurl['multiSRC'];
        $arrOrderSRC = $objCurl['orderSRC'];
        $arrFilePath = $objCurl['arrFilePath'];
        // Base64 decode pathes
        $arrFilePath = array_map(function ($src) {
            return base64_decode($src);
        }, $arrFilePath);

        // FTP settings
        $this->root_dir = TL_ROOT;
        $this->ftp_hostname = 'kletterkader.com';
        $this->ftp_username = 'aeracing';
        $this->ftp_password = 'mammut2007';

        // Open FTP connection
        $connId = $this->openFtpConnection();

        // Remote dir
        $remoteDir = 'public_html/kletterkader.com';

        // Clone arrays
        $arrMultiSRCNew = $arrMultiSRC;
        $arrOrderSRCNew = $arrOrderSRC;

        // Traverse file by file
        foreach ($arrMultiSRC as $strUuid)
        {
            $file = $arrFilePath[$strUuid];

            if ($file == '' || $file == '.' || $file == '..')
            {
                continue;
            }
            $targetSrc = $file;
            $remoteSrc = $remoteDir . '/' . $file;

            // Create folder, if it does not exist
            new Folder(dirname($file));

            // Download file from remote server and move it to the destination
            $this->downloadFileFromFtp($connId, $targetSrc, $remoteSrc);

            // Get destination model
            $objFileModel = Dbafs::addResource($file, true);
            $strNewUuid = StringUtil::binToUuid($objFileModel->uuid);
            if (($i = array_search($strUuid, $arrMultiSRCNew)) !== false)
            {
                $arrMultiSRCNew[$i] = $strNewUuid;
            }
            if (($i = array_search($strUuid, $arrOrderSRCNew)) !== false)
            {
                $arrOrderSRCNew[$i] = $strNewUuid;
            }

            echo $file . '<br>';
        }

        // Save settings to the content element
        if (($objContent = ContentModel::findByPk(28)) !== false)
        {
            // Convert uuids to bin
            $arrMultiSRCNewBin = array_map(function ($src) {
                return StringUtil::uuidToBin($src);
            }, $arrMultiSRCNew);

            // Convert uuids to bin
            $arrOrderSRCNewBin = array_map(function ($src) {
                return StringUtil::uuidToBin($src);
            }, $arrOrderSRCNew);

            $objFiles = FilesModel::findMultipleByUuids($arrMultiSRCNewBin);
            while ($objFiles->next())
            {
                // Test, if file exists
                //echo $objFiles->path . '<br>';
            }

            $objContent->multiSRC = serialize($arrMultiSRCNewBin);
            $objContent->orderSRC = serialize($arrOrderSRCNewBin);
            $objContent->save();
        }
        die();

        \ftp_close($connId);
    }

    /**
     * @return resource
     * @throws \Exception
     */
    private function openFtpConnection()
    {
        $connId = \ftp_connect($this->ftp_hostname);
        if (!\ftp_login($connId, $this->ftp_username, $this->ftp_password) || !$connId)
        {
            $msg = 'Could not establish ftp connection to ' . $this->ftp_hostname;
            if ($this->testMode) echo $msg . '<br>';
            $this->log($msg,
                __FILE__ . ' Line: ' . __LINE__,
                TL_ERROR
            );
            throw new \Exception($msg);
        }
        if ($this->testMode) echo 'Open FTP Connection with: ' . $this->ftp_hostname . '<br><br>';
        return $connId;
    }

    /**
     * Download files from ftp server
     * @param $connId
     * @param $localFile
     * @param $remoteFile
     * @return bool
     * @throws \Exception
     */
    private function downloadFileFromFtp($connId, $localFile, $remoteFile)
    {
        $connId = \ftp_get($connId, $localFile, $remoteFile, FTP_BINARY);
        if (!$connId)
        {
            $msg = 'Could not find/download ' . $remoteFile . ' from ' . $this->ftp_hostname;
            if ($this->testMode) echo $msg . '<br>';
            $this->log($msg,
                __FILE__ . ' Line: ' . __LINE__,
                TL_ERROR
            );
            throw new \Exception($msg);
        }
        return $connId;
    }

    /**
     * @param $text
     * @param $method
     * @param $type
     */
    private function log($text, $method, $type)
    {
        $adapter = $this->framework->getAdapter(System::class);
        $adapter->log($text, $method, $type);
    }

}