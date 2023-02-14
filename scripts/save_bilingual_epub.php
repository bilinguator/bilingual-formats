<?php

function delTree ($dir) {
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $dirFileAddress = "$dir/$file";
        (is_dir($dirFileAddress)) ? delTree($dirFileAddress) : unlink($dirFileAddress);
    }
    return rmdir($dir);
}

function saveBilingualEpub ($fileAddress1, $fileAddress2,
    $outputFileAddress = '.' . DIRECTORY_SEPARATOR . 'bilingual.epub',
    $coverAddress = '', $picsFolder = '', $lang1 = '', $lang2 = '', $id = '') {

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
    $titleCouple = "$title1 / $title2";
    
    $titleRest1 = @explode('<delimiter>', $textArray1[1])[1];
    $titleRest2 = @explode('<delimiter>', $textArray2[1])[1];

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

    // Indexes of header articles

    $h1Array = [];

    for ($i = 2; $i < count($textArray1); $i++) {
        if (strpos($textArray1[$i], '<h1>') !== false && strpos($textArray2[$i], '<h1>') !== false) {
            array_push($h1Array, $i);
        }
    }
    array_push($h1Array, count($textArray1));
    
    $chaptersArray1 = [];
    $chaptersArray2 = [];
    $firstChapterIndex = 0;

    if (count($h1Array) > 1) {
        for ($i = 0; $i < count($h1Array) - 1; $i++) {
            $chaptersArray1[$i] = array_slice($textArray1, $h1Array[$i], $h1Array[$i + 1] - $h1Array[$i]);
            $chaptersArray2[$i] = array_slice($textArray2, $h1Array[$i], $h1Array[$i + 1] - $h1Array[$i]);
        }

        if ($h1Array[0] != 2) {
            $firstChapterIndex = 1;

            array_unshift($chaptersArray1, array_slice($textArray1, 2, $h1Array[0] - 1));
            array_unshift($chaptersArray2, array_slice($textArray2, 2, $h1Array[0] - 1));
            array_unshift($h1Array, 2);
        }
    }

    if (count($h1Array) == 1) {
        $chaptersArray1[0] = array_slice($textArray1, 2, count($textArray1) - 2);
        $chaptersArray2[0] = array_slice($textArray2, 2, count($textArray2) - 2);
        $firstChapterIndex = 1;
    }

    // cover.html

    $coverhtml = <<<HTML
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>$titleCouple</title>
        <link rel="stylesheet" href="style.css" type="text/css" />
    </head>
    <body>
        <h1>$titleCouple</h1>
        <img src="cover.png">
    </body>
    </html>
    HTML;
    
    $chaptershtml = [];
    $imgCount = 0;

    // If first part has no header
    if ($firstChapterIndex == 1) {
        $chaptershtml[0] = <<<HTML
        <html xmlns="http://www.w3.org/1999/xhtml" lang="$lang1">
        <head>
            <title></title>
            <link rel="stylesheet" href="style.css" type="text/css" />
        </head>
        <body>
        
        HTML;

        for ($i = 0; $i < count($chaptersArray1[0]) - 1; $i++) {
            if (strpos($chaptersArray1[0][$i], '<img') !== false && $chaptersArray1[0][$i] === $chaptersArray2[0][$i]) {
                $imgIndex = explode('<img', $chaptersArray1[0][$i])[1];
                $imgIndex = explode('>', $imgIndex)[0];
                $chaptershtml[0] .= <<<HTML
                    <img src="$imgIndex.png">
                    <br />
                
                HTML;
                $imgCount++;
            } else {
                if (strpos($chaptersArray1[0][$i], '<img') !== false) {
                    $imgIndex = explode('<img', $chaptersArray1[0][$i])[1];
                    $imgIndex = explode('>', $imgIndex)[0];
                    $chaptershtml[0] .= <<<HTML
                    <img src="$imgIndex.png">
                    <br />
                
                    HTML;
                    $imgCount++;
                } else {
                    $chaptershtml[0] .= <<<HTML
                        <p lang="$lang1">{$chaptersArray1[0][$i]}</p>
                        <br />
                    
                    HTML;
                }

                if (strpos($chaptersArray2[0][$i], '<img') !== false) {
                    $imgIndex = explode('<img', $chaptersArray2[0][$i])[1];
                    $imgIndex = explode('>', $imgIndex)[0];
                    $chaptershtml[0] .= <<<HTML
                        <img src="$imgIndex.png">
                        <br />
                    
                    HTML;
                    $imgCount++;
                } else {
                    $chaptershtml[0] .= <<<HTML
                        <p lang="$lang2">{$chaptersArray2[0][$i]}</p>
                        <br />
                    
                    HTML;
                }
            }
        }
        $chaptershtml[0] .= <<<HTML
        </body>
        </html>
        HTML;
    }

    $title;

    for ($i = $firstChapterIndex; $i < count($chaptersArray1); $i++) {
        $title = str_replace(['<h1>', '</h1>'], '', $chaptersArray1[$i][0]);
        $title .= ' / ' . str_replace(['<h1>', '</h1>'], '', $chaptersArray2[$i][0]);
        $chaptershtml[$i] = <<<HTML
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
            <title>$title</title>
            <link rel="stylesheet" href="style.css" type="text/css" />
        </head>
        <body>
            <h1>$title</h1>
            <br />
        
        HTML;
        
        for ($j = 1; $j < count($chaptersArray1[$i]); $j++) {
            if (strpos($chaptersArray1[$i][$j], '<img') !== false && $chaptersArray1[$i][$j] == $chaptersArray2[$i][$j]) {
                $imgIndex = explode('<img', $chaptersArray1[$i][$j])[1];
                $imgIndex = explode('>', $imgIndex)[0];
                $chaptershtml[$i] .= <<<HTML
                    <img src="$imgIndex.png">
                    <br />

                HTML;
                $imgCount++;
            } else {
                if (strpos($chaptersArray1[$i][$j], '<img') !== false) {
                    $imgIndex = explode('<img', $chaptersArray1[$i][$j])[1];
                    $imgIndex = explode('>', $imgIndex)[0];
                    $chaptershtml[$i] .= <<<HTML
                        <img src="$imgIndex.png">
                        <br />
                    
                    HTML;
                    $imgCount++;
                } else {
                    $chaptershtml[$i] .= <<<HTML
                        <p lang="$lang1">{$chaptersArray1[$i][$j]}</p>
                        <br />
                        
                    HTML;
                }
            
                if (strpos($chaptersArray2[$i][$j], '<img') !== false) {
                    $imgIndex = explode('<img', $chaptersArray2[$i][$j])[1];
                    $imgIndex = explode('>', $imgIndex)[0];
                    $chaptershtml[$i] .= <<<HTML
                        <img src="$imgIndex.png">
                        <br />
                    
                    HTML;
                    $imgCount++;
                } else {
                    $chaptershtml[$i] .= <<<HTML
                        <p lang="$lang2">{$chaptersArray2[$i][$j]}</p>
                        <br />
                    
                    HTML;
                }
            }
        }
        $chaptershtml[$i] .= <<<HTML
        </body>
        </html>
        HTML;
    }

    // Remove <delimiter>

    for ($i = 0; $i < count($chaptershtml); $i++) {
        $chaptershtml[$i] = str_replace(PHP_EOL . '    <p><delimiter></p>' . PHP_EOL, PHP_EOL, $chaptershtml[$i]);
        $chaptershtml[$i] = str_replace('<delimiter>', '<br />', $chaptershtml[$i]);
    }

    $last_chapter = count($chaptershtml) - 1;
    $chaptershtml[$last_chapter] .= <<<HTML
    </body>
    </html>
    HTML;

    $replace = <<<HTML
    <body>
        <p>$titleRest1</p>
        <br />
        <p>$titleRest2</p>
        <br />

    HTML;
    $chaptershtml[0] = str_replace('<body>' . PHP_EOL, $replace, $chaptershtml[0]);

    for ($i = 0; $i < count($chaptershtml); $i++) {
        $replace = <<<HTML
            <p></p>
            <br />

        HTML;
        $chaptershtml[$i] = str_replace($replace, '', $chaptershtml[$i]);
    }

    // toc.ncx

    $date = date('Y-m-d');

    $tocncx = <<<XML
    <\?xml version="1.0" encoding="utf-8"\?>
    <!DOCTYPE ncx PUBLIC "-//NISO//DTD ncx 2005-1//EN" "http://www.daisy.org/z3986/2005/ncx-2005-1.dtd">
    <ncx xmlns="http://www.daisy.org/z3986/2005/ncx/" version="2005-1">
        <head>
            <meta name="dtb:uid" content="$id"/>
            <meta name="dtb:depth" content="1"/>
            <meta name="dtb:totalPageCount" content="0"/>
            <meta name="dtb:maxPageNumber" content="0"/>
        </head>
        <docTitle>
            <text>$titleCouple</text>
        </docTitle>
        <navMap>
            <navPoint id="point-1" playOrder="1">
                <navLabel>
                    <text>$titleCouple</text>
                </navLabel>
                <content src="cover.html"/>
            </navPoint>

    XML;

    $num = 1;
    for ($i = $firstChapterIndex; $i < count($chaptersArray1); $i++) {
        $num++;
        $next = $i + 1;

        $implode1 = implode('', preg_split('/<\/?h1>/', $chaptersArray1[$i][0]));
        $implode2 = implode('', preg_split('/<\/?h1>/', $chaptersArray2[$i][0]));

        $tocncx .= <<<XML
                <navPoint id="point-$num" playOrder="$num">
                    <navLabel>
                        <text>$implode1 / $implode2</text>
                    </navLabel>
                    <content src="chapter$next.html"/>
                </navPoint>
        
        XML;
    }

    $tocncx .= <<<XML
        </navMap>
    </ncx>
    XML;

    $tocncx = stripslashes($tocncx);

    // content.opf

    $contentopf = <<<XML
    <\?xml version="1.0" encoding="UTF-8"\?>
    <package xmlns="http://www.idpf.org/2007/opf" unique-identifier="BookId" version="2.0">
        <metadata xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:opf="http://www.idpf.org/2007/opf">
            <dc:title>$titleCouple</dc:title>
            <dc:creator>$authorsCouple</dc:creator>
            <dc:identifier id="BookId">$id</dc:identifier>
            <dc:date>$date</dc:date>
            <dc:publisher>Bilinguator</dc:publisher>
            <dc:language>$lang1</dc:language>
            <dc:language>$lang2</dc:language>
            <meta name="cover" content="cover-image"/>
        </metadata>
        <manifest>
            <item id="ncx" href="toc.ncx" media-type="application/x-dtbncx+xml" />
            <item id="cover-image" href="cover.png" media-type="image/png" />
            <item id="cover" href="cover.html" media-type="application/xhtml+xml" />
            <item id="style" href="style.css" media-type="text/css" />

    XML;

    for ($i = 0; $i < count($chaptersArray1); $i++) {
        $next = $i + 1;
        $contentopf .= <<<XML
                <item id="chapter$next" href="chapter$next.html" media-type="application/xhtml+xml" />
        
        XML;
    }

    for ($i = 1; $i <= $imgCount; $i++) {
        $contentopf .= <<<XML
                <item id="picture$i" href="$i.png" media-type="image/png" />
        
        XML;
    }

    $contentopf .= <<<XML
            <item id="logo" href="logo.png" media-type="image/png" />
        </manifest>
        <spine toc="ncx">
            <itemref idref="cover" linear="no" />

    XML;

    for ($i = 0; $i < count($chaptersArray1); $i++) {
        $next = $i + 1;
        $contentopf .= <<<XML
                <itemref idref="chapter$next" />

        XML;
    }

    $contentopf .= <<<XML
        </spine>
        <guide>
            <reference href="cover.html" title="Cover" type="cover"/>
        </guide>
    </package>
    XML;

    $contentopf = stripslashes($contentopf);

    // style.css

    $stylecss = <<<CSS
    * {
        text-decoration: none;
        font-family: "Helvetica Neue", "Open Sans", Calibri, Helvetica, Arial, sans-serif;
    }
    h1, title {
        color: #00A864;
        text-align: center;
    }
    b {
        color: #00A864;
    }
    CSS;

    // File container.xml

    $containerxml = <<<XML
    <?xml version="1.0"?>
    <container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container">
        <rootfiles>
            <rootfile full-path="OPS/content.opf" media-type="application/oebps-package+xml" />
        </rootfiles>
    </container>
    XML;

    // Save files
    
    header('Content-Type: text/html; charset=utf-8');
    
    mkdir("$outputFileAddress.tmp", 0777);
    mkdir("$outputFileAddress.tmp/META-INF/", 0777);
    mkdir("$outputFileAddress.tmp/OPS/", 0777);
    
    file_put_contents("$outputFileAddress.tmp/mimetype", 'application/epub+zip');
    file_put_contents("$outputFileAddress.tmp/META-INF/container.xml", $containerxml);
    file_put_contents("$outputFileAddress.tmp/OPS/style.css", $stylecss);
    file_put_contents("$outputFileAddress.tmp/OPS/content.opf", $contentopf);
    file_put_contents("$outputFileAddress.tmp/OPS/toc.ncx", $tocncx);
    file_put_contents("$outputFileAddress.tmp/OPS/cover.html", $coverhtml);
    
    if (file_exists($coverAddress)) {
        copy($coverAddress, "$outputFileAddress.tmp/OPS/cover.png");
    }
    
    for ($i = 1; $i <= $imgCount; $i++) {
        $imgAddress = "$picsFolder/$i.png";
        if (file_exists($imgAddress)) {
            copy($imgAddress, "$outputFileAddress.tmp/OPS/$i.png");
        }
    }
    
    for ($i = 0; $i < count($chaptershtml); $i++) {
        $chaptershtml[$i] = stripslashes($chaptershtml[$i]);
        $next = $i + 1;
        file_put_contents("$outputFileAddress.tmp/OPS/chapter$next.html", $chaptershtml[$i]);
    }

    // Create epub file
    
    $epubFile = new ZipArchive(ZipArchive::CM_STORE);
    $epubFile->open($outputFileAddress, ZipArchive::CREATE);
    $epubFile->addFile("$outputFileAddress.tmp/mimetype", 'mimetype');
    
    $opsList = array_diff(scandir("$outputFileAddress.tmp/OPS/"), ['.', '..']);
    foreach ($opsList as $file) {
        $epubFile->addFile("$outputFileAddress.tmp/OPS/$file", "OPS/$file");
    }
    
    $metaInfList = array_diff(scandir("$outputFileAddress.tmp/META-INF/"), ['.', '..']);
    foreach ($metaInfList as $file) {
        $epubFile->addFile("$outputFileAddress.tmp/META-INF/$file", "META-INF/$file");
    }
    
    $epubFile->close();
    
    delTree("$outputFileAddress.tmp");
}
