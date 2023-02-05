<?php

function saveBilingualTxt ($fileAddress1, $fileAddress2,
    $outputFileAddress = '.' . DIRECTORY_SEPARATOR . 'bilingual.txt') {

    if (file_exists($fileAddress1) && file_exists($fileAddress2)) {
        $outputDirectory = explode(DIRECTORY_SEPARATOR, $outputFileAddress);
        $outputDirectory = array_slice($outputDirectory, 0, count($outputDirectory) - 1);
        $outputDirectory = implode(DIRECTORY_SEPARATOR, $outputDirectory);
        
        if (!is_dir($outputDirectory)) {
            throw new Exception("Directory $outputDirectory not found!");
        }

        $fileContent1 = file_get_contents($fileAddress1);
        $fileContent2 = file_get_contents($fileAddress2);

        $fileCoding1 = mb_detect_encoding($fileContent1, ['UTF-8', 'ASCII', 'CP1251']);
        $fileCoding2 = mb_detect_encoding($fileContent2, ['UTF-8', 'ASCII', 'CP1251']);

        $textArray1 = preg_split("/[\r]*[\n]+/", iconv($fileCoding1, 'UTF-8', $fileContent1));
        $textArray2 = preg_split("/[\r]*[\n]+/", iconv($fileCoding2, 'UTF-8', $fileContent2));

        $maxLength = max(count($textArray1), count($textArray2));
        $txtContent = '';

        for ($i = 0; $i < $maxLength; $i++) {
            $txtContent .= trim(@$textArray1[$i]) . PHP_EOL . PHP_EOL;
            $txtContent .= trim(@$textArray2[$i]) . PHP_EOL . PHP_EOL;
        }

        $txtContent = preg_replace("/<\/?(h1|b|i|img[0-9]+)>/i", '', $txtContent);
        $txtContent = str_replace('<delimiter>', PHP_EOL, $txtContent);
        $txtContent = preg_replace("/" . PHP_EOL . "{4,6}/", str_repeat(PHP_EOL, 2), $txtContent);

        file_put_contents($outputFileAddress, trim($txtContent));
    } else {
        if (!file_exists($fileAddress1) && !file_exists($fileAddress2)) {
            throw new Exception("Files $fileAddress1 and $fileAddress2 not found!");
        } else if (!file_exists($fileAddress1)) {
            throw new Exception("File $fileAddress1 not found!");
        } else {
            throw new Exception("File $fileAddress2 not found!");
        }
    }
}
