<?php

function saveBilingualTxt ($fileAddress1, $fileAddress2, $outputFileAddress = './bilingual.txt') {

    if (file_exists($fileAddress1) && file_exists($fileAddress2)) {
        $fileContent1 = file_get_contents($fileAddress1);
        $fileContent2 = file_get_contents($fileAddress2);

        $fileCoding1 = mb_detect_encoding($fileContent1, array('utf-8', 'cp1251'));
        $fileCoding2 = mb_detect_encoding($fileContent2, array('utf-8', 'cp1251'));

        $textArray1 = preg_split("/[\r]*[\n]+/", iconv($fileCoding1, 'UTF-8', $fileContent1));
        $textArray2 = preg_split("/[\r]*[\n]+/", iconv($fileCoding2, 'UTF-8', $fileContent2));

        foreach ([$textArray1, $textArray2] as $textArray) {
            foreach ($textArray as $key => $value) {
                foreach (['<h1>', '</h1>', '<b>', '</b>', '<i>', '</i>'] as $tag) {
                    $value = implode('', explode($tag, $value));
                }
                $value = implode('\n', explode('<delimiter>', $value));
            }
        }

        $maxLength = max(count($textArray1), count($textArray2));
        $txtContent = '';
        $tagsArray = ['<h1>', '</h1>', '<b>', '</b>', '<i>', '</i>'];

        for ($i = 0; $i < $maxLength; $i++) {
            if (strpos($textArray1[$i], '<img') === false) {
                foreach ($tagsArray as $tag) {
                    $textArray1[$i] = implode('', explode($tag, $textArray1[$i]));
                }
                $textArray1[$i] = implode(PHP_EOL, explode('<delimiter>', $textArray1[$i]));
                $txtContent .= trim($textArray1[$i]) . PHP_EOL . PHP_EOL;
            }
            if (strpos($textArray2[$i], '<img') === false) {
                foreach (['<h1>', '</h1>', '<b>', '</b>', '<i>', '</i>'] as $tag) {
                    $textArray2[$i] = implode('', explode($tag, $textArray2[$i]));
                }
                $textArray2[$i] = implode(PHP_EOL, explode('<delimiter>', $textArray2[$i]));
                $txtContent .= trim($textArray2[$i]) . PHP_EOL . PHP_EOL;
            }
        }

        file_put_contents($outputFileAddress, trim($txtContent));
    } else {
        if (!file_exists($fileAddress1)) {
            throw new Exception("File {$fileAddress1} not found.");
        }
        if (!file_exists($fileAddress2)) {
            throw new Exception("File {$fileAddress2} not found.");
        }
    }
}
