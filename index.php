<?php
require_once('vendor/autoload.php');

use Symfony\Component\Yaml\Parser;

// I NEED TO OPEN THE RIGHT MODEL FOR EACH ITERRATION

// parse the file
$yaml = new Parser();
$content = $yaml->parse(file_get_contents('content.yaml'));

// array of regions
$regions = explode(";", $content['regions']);

$linksFileHandle = fopen('footer-links.php', 'a');
fwrite($linksFileHandle, (new \DateTime())->format("Y-M-D h:m:s") . "-----------------------" . PHP_EOL);
// display some messages for the user, numbers of models , regions, how many combinations are there, and the count of files that were generated


// iterate over all regions
for ($regionIndex = 0; $regionIndex < count($regions); $regionIndex++ )
{
    // iterate over the models
    foreach ($content as $key => $value)
    {
        // if it's the regions "which is the last thing in content array", just ignore it
        if ($key === 'regions')
            break;

        // get the correct model number; ex:1
        $modelNumber = mb_substr($key, -1);

        // Open the correct model file
        $modelName = 'model' . $modelNumber . '.php';
        $handle = fopen($modelName, 'r');
        $data = fread($handle,filesize($modelName));

        // get the keywords out into an array or something :)
        $keywords = explode(";", $value['keywords']);
        // iterate over all the values and dump for now
        for ($index = 0; $index < count($keywords); $index++) {
            $keyword_string = $keywords[$index];
            $region_string = $regions[$regionIndex];

            // create a new file and save it to the disk
            // replace the weird french characters with just regular "english" characters

            $newFilename = mb_strtolower($keyword_string) . " " . mb_strtolower($region_string);
            
            // get rid of the "french" special characters
            $newFilename = str_replace(
                                        ["é", "è", "ê", "à", "â", "ô", "ù", "û", "î", "ç", "ï"],
                                        ["e", "e", "e", "a", "a", "o", "u", "u", "i", "c", "i"],
                                        str_replace(' ', '-', $newFilename) .'.php'
                                        );

            $newHandle = fopen($newFilename, 'w');
            // replace the data on the model file.
            $newContent = str_replace(['$motcle', '$ville'], [ucfirst($keyword_string), ucfirst($region_string)], $data);
            fwrite($newHandle, $newContent);
            fclose($newHandle);

            // append the created link to the footer-links file
            // wrap the link inside an <li> because usually a <ul> is used to hold all the generated links
            $link = '<li><a href="' . $newFilename . '">' . $keyword_string. ' ' . $region_string . '</a></li>' . PHP_EOL;
            fwrite($linksFileHandle, $link);
        }
        // closing the file
        fclose($handle);
        // appending an empty like just for aestethic reasons
        fwrite($linksFileHandle, PHP_EOL);
    }
}
fclose($linksFileHandle);