<?php

namespace James2001;

class Zxing
{
    private $key;
    private $options;
    private $libBinPath;
    private $javaSeparator;

    public function __construct($key = "", $options = "", $libBinPath = "")
    {
        if(!$libBinPath){
            $libBinPath = dirname(__FILE__). DIRECTORY_SEPARATOR . "../bin_lib";
        }

        $this->key = $key;
        $this->options = $options;
        $this->libBinPath = $libBinPath;
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->javaSeparator = ';';
        } else {
            $this->javaSeparator = ':';
        }

    }

    /**
     * The key would be start qrcode
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * The key would be start qrcode
     *
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * List of option
     *
     * @return string
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * --try_harder: Use the TRY_HARDER hint, default is normal (mobile) mode
     * --pure_barcode: Input image is a pure monochrome barcode image, not a photo
     * --products_only: Only decode the UPC and EAN families of barcodes
     * --dump_results: Write the decoded contents to input.txt
     * --dump_black_point: Compare black point algorithms as input.mono.png
     * --multi: Scans image for multiple barcodes
     * --brief: Only output one line per file, omitting the contents
     * --recursive: Descend into subdirectories
     * --crop=left,top,width,height: Only examine cropped region of input image(s)
     * --possibleFormats=barcodeFormat[,barcodeFormat2...] where barcodeFormat is any
     * of: AZTEC,CODABAR,CODE_39,CODE_93,CODE_128,DATA_MATRIX,EAN_8,EAN_13,ITF,MAXICODE,
     * PDF_417,QR_CODE,RSS_14,RSS_EXPANDED,UPC_A,UPC_E,UPC_EAN_EXTENSION
     *
     * ex: --try_harder --multi
     *
     * @param string $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * The path for core.jar and javase.jar
     *
     * @return string
     */
    public function getLibBinPath()
    {
        return $this->libBinPath;
    }

    /**
     * @param string $libBinPath
     */
    public function setLibBinPath($libBinPath)
    {
        $this->libBinPath = $libBinPath;
    }

    /**
     * Find all or one only Qrcode(s) stared with key
     *
     * @param $image_path the path of the image to be scanned for qrCode
     * @param $firstOnly only return the first qrcode
     * @return mixed
     */
    public function find($image_path, $firstOnly = false) {
        $cmd = 'java -cp ' . $this->libBinPath . DIRECTORY_SEPARATOR . 'javase.jar';
        $cmd .= $this->javaSeparator . $this->libBinPath . DIRECTORY_SEPARATOR;
        $cmd .= 'core.jar com.google.zxing.client.j2se.CommandLineRunner ' . $image_path;
        $cmd .= " --multi " . $this->options;
        $output = [];
        exec($cmd, $output, $return_var);

        $return = [];
        if (($return_var === 0) && is_array($output)) {
            while(($parsedIndex = array_search('Parsed result:', $output)) !== false) {
                $qrValueIndex = $parsedIndex + 1;
                $qrValue = $output[$qrValueIndex];

                if (empty($this->key) || (strpos($qrValue, $this->key) !== false)) {
                    $qrValue = str_replace($this->key, '', $output[$qrValueIndex]);

                    if ($firstOnly) {
                        return $qrValue;
                    }
                    $return[] = $qrValue;
                }

                unset($output[$parsedIndex]);
            }
        }

        return (!empty($return)) ? $return : false;
    }

    /**
     * Find the first Qrcode stared with key
     *
     * @param $image_path
     * @return string
     */
    public function findFirst($image_path)
    {
        return $this->find($image_path, true);
    }

    /**
     * Find all Qrcodes stared with key
     *
     * @author halfred
     * @param $image_path
     * @return array
     */
    public function findMulti($image_path)
    {
        return $this->find($image_path);
    }
}
