<?php

require_once('../save_bilingual_txt.php');

# Test 0.0 - Normal
$fileAddress1 = "./le_petit_prince_fr.txt";
$fileAddress2 = "./le_petit_prince_de.txt";
$outputFileAddress = "./results/le_petit_prince_fr_de.0.0.txt";
saveBilingualTxt($fileAddress1, $fileAddress2, $outputFileAddress);

# Test 0.1 - Input file 2 is shoter that input file 1
$fileAddress1 = "./le_petit_prince_fr.txt";
$fileAddress2 = "./le_petit_prince_de.short.txt";
$outputFileAddress = "./results/le_petit_prince_fr_de.0.1.txt";
saveBilingualTxt($fileAddress1, $fileAddress2, $outputFileAddress);

require_once('../save_bilingual_fb2.php');

# Test 1.0
$fileAddress1 = "./le_petit_prince_fr.txt";
$fileAddress2 = "./le_petit_prince_de.txt";
$outputFileAddress = "./results/le_petit_prince_fr_de.1.0.fb2";
$coverAddress = "./img/cover.png";
$picsFolder = "./img";
$lang1 = 'fr';
$lang2 = 'de';
$id = 'le_petit_prince.test.1.0';
saveBilingualFb2($fileAddress1, $fileAddress2, $outputFileAddress,
    $coverAddress, $picsFolder, $lang1, $lang2, $id);

# Test 1.1 - Wrong folder for cover and pictures 
$fileAddress1 = "./le_petit_prince_fr.txt";
$fileAddress2 = "./le_petit_prince_de.txt";
$outputFileAddress = "./results/le_petit_prince_fr_de.1.1.fb2";
$coverAddress = "./pics/cover.png";
$picsFolder = "./pics";
$lang1 = 'fr';
$lang2 = 'de';
$id = 'le_petit_prince.test.1.1';
saveBilingualFb2($fileAddress1, $fileAddress2, $outputFileAddress,
    $coverAddress, $picsFolder, $lang1, $lang2, $id);

# Test 1.2 - Input file 2 is shoter that input file 1
$fileAddress1 = "./le_petit_prince_fr.txt";
$fileAddress2 = "./le_petit_prince_de.short.txt";
$outputFileAddress = "./results/le_petit_prince_fr_de.1.2.fb2";
$coverAddress = "./img/cover.png";
$picsFolder = "./img";
$lang1 = 'fr';
$lang2 = 'de';
$id = 'le_petit_prince.test.1.2';
saveBilingualFb2($fileAddress1, $fileAddress2, $outputFileAddress,
    $coverAddress, $picsFolder, $lang1, $lang2, $id);


require_once('../save_bilingual_epub.php');

# Test 2.0 - Normal
$fileAddress1 = "./le_petit_prince_fr.txt";
$fileAddress2 = "./le_petit_prince_de.txt";
$outputFileAddress = "./results/le_petit_prince_fr_de.2.0.epub";
$coverAddress = "./img/cover.png";
$picsFolder = "./img";
$lang1 = 'fr';
$lang2 = 'de';
$id = 'le_petit_prince.test.2.0';

saveBilingualEpub($fileAddress1, $fileAddress2, $outputFileAddress,
    $coverAddress, $picsFolder, $lang1, $lang2, $id);

# Test 2.1 - Wrong folder for cover and pictures 
$fileAddress1 = "./le_petit_prince_fr.txt";
$fileAddress2 = "./le_petit_prince_de.txt";
$outputFileAddress = "./results/le_petit_prince_fr_de.2.1.epub";
$coverAddress = "./pics/cover.png";
$picsFolder = "./pics";
$lang1 = 'fr';
$lang2 = 'de';
$id = 'le_petit_prince.test.2.1';

saveBilingualEpub($fileAddress1, $fileAddress2, $outputFileAddress,
    $coverAddress, $picsFolder, $lang1, $lang2, $id);

# Test 2.2 - Input file 2 is shoter that input file 1
$fileAddress1 = "./le_petit_prince_fr.txt";
$fileAddress2 = "./le_petit_prince_de.short.txt";
$outputFileAddress = "./results/le_petit_prince_fr_de.2.2.epub";
$coverAddress = "./img/cover.png";
$picsFolder = "./img";
$lang1 = 'fr';
$lang2 = 'de';
$id = 'le_petit_prince.test.2.2';

saveBilingualEpub($fileAddress1, $fileAddress2, $outputFileAddress,
    $coverAddress, $picsFolder, $lang1, $lang2, $id);
