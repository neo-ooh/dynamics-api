<?php

namespace App\Http\Controllers;

use App\NewsSubject;
use function count;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use function in_array;
use Nathanmac\Utilities\Parser\Parser;
use function simplexml_load_file;


class NewsController extends Controller
{
    /**
     * Refresh will gather records from the Canadian Press and update the database accordingly.
     * New records will be inserted in the DDB, older ones will be removed, as well as their corresponding media if present
     */
    public function refresh() {
        // Start by getting all the subjects to parse
        $newsSubjects = NewsSubject::all();

        // Get the Canadian Press Storage
        $cpStorage = Storage::disk('canadian-press');

        $parser = new Parser();

        // Refresh the list of article for each subject
        foreach($newsSubjects as $subject) {
            // Get all the records for this subject
            $subjectRecords = $subject->records();

            // Get all the files in the canadian-press subject directory
            $cpFiles = $cpStorage->files($subject->slug);

            // Filter to only get articles (XML Files)
            $cpArticles = array_filter($cpFiles, function ($item) { return strpos($item, '.xml'); });

            // Parse each article, insert/update it in the database and copy its image if it exist and isn't already stored
            foreach ($cpArticles as $article) {
                // Parse the xml file
                $articleXML = simplexml_load_string($cpStorage->get($article));

                $articleInfos = [
                    'id' => (string)$articleXML->xpath('//doc-id/@id-string')[0],
                    'date' => (string)$articleXML->xpath('//story.date/@norm')[0],
                    'headline' => (string)$articleXML->xpath('//hl1')[0],
                    'media' => $articleXML->xpath('//media-reference/@source'),
                ];

                // Select the image if there is multiple ones, and check its availability
                if(count($articleInfos['media']) > 0) {
                    $mediaName = (string)$articleInfos['media'][0];
                    $articleInfos['media'] = in_array($subject->slug.'/'.$mediaName, $cpFiles) ? $mediaName : null;
                } else {
                    $articleInfos['media'] = null;
                }

                return new Response([$articleInfos, $article, $subjectRecords, $article]);
            }
        }
    }
}
