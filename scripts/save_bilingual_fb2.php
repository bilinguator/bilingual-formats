<?php

function saveBilingualFb2 ($fileAddress1, $fileAddress2,
    $outputFileAddress = '.' . DIRECTORY_SEPARATOR . 'bilingual.fb2',
    $coverAddress = '', $picsFolder = '', $srcLang = '', $lang = '', $id = '') {

    if (!file_exists($fileAddress1) && !file_exists($fileAddress2)) {
        throw new Exception("Files $fileAddress1 and $fileAddress2 not found!");
    } else if (!file_exists($fileAddress1)) {
        throw new Exception("File $fileAddress1 not found!");
    } else if (!file_exists($fileAddress2)) {
        throw new Exception("File $fileAddress2 not found!");
    }

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

    $author1 = $textArray1[0];
    $author2 = $textArray2[0];

    $title1 = str_replace(['<h1>', '</h1>'], '', explode('<delimiter>', $textArray1[1])[0]);
    $title2 = str_replace(['<h1>', '</h1>'], '', explode('<delimiter>', $textArray2[1])[0]);
    
    $titleRest1 = explode('<delimiter>', $textArray1[1])[1];
    $titleRest2 = explode('<delimiter>', $textArray2[1])[1];

    $authorsCouple = '';
    $authorTitle = '';

    if ($author1 !== '<delimiter>' && $author2 !== '<delimiter>') {
        $authorsCouple = "$author1 / $author2";
        $authorTitle = "<p>$authorsCouple</p>";
    } else if ($author1 !== '<delimiter>' && $author2 === '<delimiter>') {
        $authorsCouple = $author1;
        $authorTitle = "<p>$authorsCouple</p>";
    } else if ($author1 === '<delimiter>' && $author2 !== '<delimiter>') {
        $authorsCouple = $author2;
        $authorTitle = "<p>$authorsCouple</p>";
    }

    $date = date('m.d.Y');
    $year = date('Y');

    $fbContent = <<<FB2
    <?xml version="1.0" encoding="UTF-8"?>
    <FictionBook xmlns="http://www.gribuser.ru/xml/fictionbook/2.0" xmlns:l="http://www.w3.org/1999/xlink">
    <description>
        <title-info>
            <genre>antique</genre>
            <author><nickname>$authorsCouple</nickname></author>
            <book-title>$title1 / $title2</book-title>
            <coverpage><image l:href="#cover"/></coverpage>
            <lang>$lang</lang>
            <src-lang>$srcLang</src-lang>
        </title-info>
        <document-info>
            <author><nickname>$authorsCouple</nickname></author>
            <program-used>B-Editor</program-used>
            <date>$date</date>
            <id>$id</id>
            <version>1.0</version>
        </document-info>
        <publish-info>
            <book-name>$title1 / $title2</book-name>
            <publisher>Bilinguator</publisher>
            <year>$year</year>
        </publish-info>
    </description>
    <body>
    <title>$authorTitle<p>$title1 / $title2</p></title>
    
    FB2;

    if ($titleRest1 !== '') {
        $fbContent .= <<<FB2
        <empty-line/>
        <p>$titleRest1</p>

        FB2;
    }
    
    if ($titleRest2 !== '') {
        $fbContent .= <<<FB2
        <empty-line/>
        <p>$titleRest2</p>

        FB2;
    }

    $imgCount = 0;

    for ($i = 2; $i < count($textArray1); $i++) {
        if (strpos($textArray1[$i], '<img') !== false && $textArray1[$i] === $textArray2[$i]) {
            $imgIndex = explode('>', explode('<img', $textArray1[$i])[1])[0];
            $fbContent .= <<<FB2
            <empty-line/>
            <p><image l:href="#$imgIndex"/></p>

            FB2;
            $imgCount++;
        } else {
            if (strpos($textArray1[$i], '<img') !== false) {
                $imgIndex = explode('>', explode('<img', $textArray1[$i])[1])[0];
                $fbContent .= <<<FB2
                <empty-line/>
                <p><image l:href="#$imgIndex"/></p>

                FB2;
                $imgCount++;
            } else {
                $textArray1[$i] = strpos($textArray1[$i], '<h1>') !== false ? $textArray1[$i] : "<p>{$textArray1[$i]}</p>";
                $fbContent .= <<<FB2
                <empty-line/>
                $textArray1[$i]

                FB2;
            }
            
            if (strpos($textArray2[$i], '<img') !== false) {
                $imgIndex = explode('>', explode('<img', $textArray2[$i])[1])[0];
                $fbContent .= <<<FB2
                <empty-line/>
                <p><image l:href="#$imgIndex"/></p>

                FB2;
                $imgCount++;
            } else {
                $textArray2[$i] = strpos($textArray2[$i], '<h1>') !== false ? $textArray2[$i] : "<p>{$textArray2[$i]}</p>";
                $fbContent .= <<<FB2
                <empty-line/>
                $textArray2[$i]

                FB2;
            }
        }
    }
    
    $fbContent = str_replace(['<i>', '</i>'], ['<emphasis>', '</emphasis>'], $fbContent);
    $fbContent = str_replace('</h1>' . PHP_EOL . '<empty-line/>' . PHP_EOL . '<h1>', ' / ', $fbContent);
    $fbContent = str_replace('<h1>', '</section>' . PHP_EOL . '<section>' . PHP_EOL . '<title>', $fbContent);

    $search = PHP_EOL . '</section>';
    $pos = strpos($fbContent, $search);
    $fbContent = $pos !== false ? substr_replace($fbContent, '', $pos, strlen($search)) : $fbContent;

    $fbContent = str_replace('</h1>', '</title>', $fbContent);
    $fbContent .= substr_count($fbContent, '<title>') > 1 ? '</section>' . PHP_EOL : '';
    $fbContent .= '</body>' . PHP_EOL;

    $fbContent = str_replace(['<delimiter>'], ['</p><p>'], $fbContent);
    $fbContent = str_replace(['<b>', '</b>'], ['<strong>', '</strong>'], $fbContent);
    $fbContent = str_replace('<p></p>' . PHP_EOL . '<empty-line/>' . PHP_EOL, '', $fbContent);

    $cover = '';
    if (file_exists($coverAddress)) {
        $cover = base64_encode(file_get_contents($coverAddress));
    }
    
    $pictures = '';
    if ($imgCount > 0) {
        for ($i = 1; $i <= $imgCount; $i++) {
            $picAddress = "$picsFolder/$i.png";
            if (file_exists($picAddress)) {
                $pic = file_get_contents($picAddress);
                $encodedPic = base64_encode($pic);
                $pictures .= <<<FB2
                <binary id="$i" content-type="image/png">$encodedPic</binary>
                FB2;
            }
        }
    }

    $fbContent = preg_replace("/" . PHP_EOL . "{4,6}/", str_repeat(PHP_EOL, 2), $fbContent);
    
    $fbContent .= <<<FB2
    <binary id="cover" content-type="image/png">$cover</binary>
    $pictures</FictionBook>
    FB2;

    file_put_contents($outputFileAddress, $fbContent);
}
