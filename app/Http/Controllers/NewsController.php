<?php

namespace App\Http\Controllers;

use App\NewsRecord;
use App\NewsSubject;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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

        // Refresh the list of article for each subject
        foreach($newsSubjects as $subject) {
            // Get all the records for this subject
            $subjectRecords = $subject->records();

            // Get all the files in the canadian-press subejct directory
            $cpFiles = $cpStorage->files($subject->slug);

            // Filter to only get articles (XML Files)
            $cpArticles = array_filter($cpFiles, function ($item) { return strpos($item, '.xml'); });

            // Parse each article, insert/update it in the database and copy its image if it exist and isn't already stored
            foreach ($cpArticles as $article) {
                // Parse the xml file
                $articleInfos = XmlParser::extract($cpStorage->get($article))->parse([
                    'headline' => ['uses' => 'body.body.head.hedline.hl1'],
                    'date' => ['uses' => 'body.body.head.dateline.story.date::norm'],
                    'media' => ['uses' => 'body.body.content.block.media.media-reference::source'],
                ]);

                return new Response($articleInfos);
            }
        }
    }
}
