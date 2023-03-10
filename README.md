![](img/banner.png)

# Bilingual formats

**Bilingual formats** is the set of PHP scripts to convert two aligned source texts into one TXT, FB2 or EPUB file with alternating paragraphs. Keep in mind that source texts are not just plain texts but have simple but rather useful [specification](#source-files-specification). The code has been developed and tested with **PHP 7.3.33**.

This is the very code [Bilinguator](https://bilinguator.com/) uses to generate bilingual books — the powerful tool to study languages.

|**Contents**|
|---|
|[Getting started](#getting-started)|
|[Functions description](#functions-description)|
|[Source files specification](#source-files-specification)|
|[Tests](#tests)|


## Getting started
Three scripts are available in the [scripts](scripts) folder:

* [**save_bilingual_txt.php**](scripts/save_bilingual_txt.php) contains [`saveBilingualTxt`](#savebilingualtxt) function;
* [**save_bilingual_fb2.php**](scripts/save_bilingual_fb2.php) contains [`saveBilingualFb2`](#savebilingualfb2) function;
* [**save_bilingual_epub.php**](scripts/save_bilingual_epub.php) contains [`saveBilingualEpub`](#savebilingualepub) function.

To start using a function from the list, download and plug in the corresponding script into your PHP code with the `require` or `require_once` function.

## Functions description

### saveBilingualTxt

saveBilingualTxt function reads two source files and creates bilingual TXT file with alternating paragraphs.

```
saveBilingualTxt(
    string $fileAddress1,
    string $fileAddress2,
    string $outputFileAddress = '.' . DIRECTORY_SEPARATOR . 'bilingual.txt'
)
```

`$fileAddress1`, `$fileAddress2` — paths to the source file 1 and 2 respectively to read.

`$outputFileAddress` — path to the file where to write the bilingual TXT file. If not specified, the output file will be saved in the working directory named as *bilingual.txt*.

### saveBilingualFb2

saveBilingualFb2 function reads two source files and creates bilingual FB2 file with alternating paragraphs.

```
saveBilingualFb2 (
    string $fileAddress1,
    string $fileAddress2,
    string $outputFileAddress = '.' . DIRECTORY_SEPARATOR . 'bilingual.fb2',
    string $coverAddress = '',
    string $picsFolder = '',
    string $srcLang = '',
    string $lang = '',
    string $id = ''
)
```

`$fileAddress1`, `$fileAddress2` — paths to the source file 1 and 2 respectively to read.

`$outputFileAddress` — path to the file where to write the bilingual FB2 file. If not specified, the output file will be saved in the working directory named as *bilingual.fb2*.

`$coverAddress` — path to the cover in the PNG format. If not specified, no cover will be added to the book.

`$picsFolder` — folder in which book illustrations in the PNG format are located. If not specified, no illustrations will be added to the book.

`$srcLang` — the language code for `<src-lang></src-lang>` tags in the FB2 books. See the specification for the FB2 format. If not specified, no source language will be added to these tags. Use ISO 639 language codes as prescribed for FB2 format. We recommend to use your first language as the source language.

`$lang` — the same as for `$srcLang` but the `<lang></lang>` tags are filled. We recommend to use your second language for these tags.

`$id` — unique ID of your book for the `<id></id>` tags. See the specification for the FB2 format for more information. If not specified, no ID will be added to these tags.

### saveBilingualEpub

saveBilingualEpub function reads two source files and creates bilingual EPUB file with alternating paragraphs.

```
saveBilingualEpub (
    $fileAddress1,
    $fileAddress2,
    $outputFileAddress = '.' . DIRECTORY_SEPARATOR . 'bilingual.epub',
    $coverAddress = '',
    $picsFolder = '',
    $lang1 = '',
    $lang2 = '',
    $id = ''
)
```

`$fileAddress1`, `$fileAddress2` — paths to the source file 1 and 2 respectively to read.

`$outputFileAddress` — path to the file where to write the bilingual EPUB file. If not specified, the output file will be saved in the working directory named as *bilingual.epub*.

`$coverAddress` — path to the cover in the PNG format. If not specified, no cover will be added to the book.

`$picsFolder` — folder in which book illustrations in the PNG format are located. If not specified, no illustrations will be added to the book.

`$lang1` — the language code for the `lang` attribute in the tags with the corresponding language. If not specified, the `lang` attributes will be left empty. For more information see the EPUB specification.

`$lang2` — the same as for `$lang1`.

`$id` — unique ID of your book.

## Source files specification

### First two lines

Source files are the plain text files of TXT (not necessary) extention.

The first two lines are reserved for the information about a book. Line 1 stands for an author. Line 2 contains a title in the `<h1></h1>` tags. If an additional information about translator, publishing house, legal notice, etc. is needed, the `<delimiter>` tag is added after the `</h1>` tag and after that followed by the information. In the scripts, this additional information is called `$titleRest1` and `$titleRest2` for the two files respectively.

Example of the first two lines of a source file:

```
Antoine de Saint-Exupéry
<h1>Der Kleine Prinz</h1><delimiter>Ins Deutsche übertragen von Grete und Josef Leitgeb
```

If no information on author and/or book title is needed, leave the `<delimiter>` tag in the line 1 and/or 2. These two lines are not included in the book body which always starts with the line 3.

```
<delimiter>
<delimiter>
```

**Do not leave the lines empty, because any empty line is eliminated from the result file! It may leed to the unexpected paragraphs shift.**

### Book body

Book body consists of the paragraphs (called articles in the code) divided by line breaks. The `<delimiter>` tag is used if the line break is typed inside the article but alignment shoud not be disturbed. Besides, there are HTML-like tags: `<h1></h1>`, `<b><\b>`, `<i></i>` which stand for headers, bold and italic styles respectively.

Illustrations can be added while creating the FB2 and EPUB files. For this, move all the illustrations to one directory, name them as natural arabiс numbers like [here](tests/img). We do not garantee if the script works correctly in case other symbols are provided in the file names. Add `<imgℕ>` tags to your source files, where `ℕ` is the natural arabic number. The entire article should contain the only tag and nothing else, for example, `<img1>`. If two corresponding articles contain the `<imgℕ>` tag with the same number, the illustration will be added only once.

Table below provides the information on how the tags are processed while creating TXT, FB2 and EPUB bilingual files.

|Tag|TXT|FB2|EPUB|
|---|---|---|---|
|`<h1>Text</h1>`|`Text`|`<title>Text</title>`|`<title>Text</title>`|
|`<b>Text</b>`|`Text`|`<strong>Text</strong>`|`<b>Text</b>`|
|`<i>Text</i>`|`Text`|`<emphasis>Text</emphasis>`|`<i>Text</i>`|
|`<delimiter>`|Line break|`</p><p>`|`<br />`|
|`<img1>`|Empty line|`<image l:href="#1"/>`|`<img src="1.png">`|

## Tests

In the [tests](tests) folder you can find the [tests.php](tests/tests.php) script with the examples of how to use the Bilingual formats scripts. Three source texts are provided in the folder. First two are the aligned versions of “Le petit prince” in French ([*le_petit_prince_fr.txt*](tests/le_petit_prince_fr.txt)) and German ([*le_petit_prince_de.txt*](tests/le_petit_prince_de.txt)). The German version contains the title rest on the second line. 47 `<imgℕ>` tags are in each of the files and 47 PNG images are stored in the [tests/img](tests/img) folder. The third source file is the shortened version of the German translation ([*le_petit_prince_de.short.txt*](tests/le_petit_prince_de.short.txt)). When the two source files have different counts of articles, the article of the longest one are alternating with empty lines and no error occurs.

The [French-German version of “Le petit prince”](https://bilinguator.com/bilingual?book=307) and many other books are available on our website: [bilinguator.com](https://bilinguator.com/).
