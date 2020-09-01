<?php

namespace App\Http\Controllers;

use App\NewsCategory;
use App\NewsRecord;
use App\NewsSubject;
use function count;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use function in_array;


class NewsController extends Controller
{
    const MEDIA_FOLDER = 'news/medias/';

    /**
     * Refresh will gather records from the Canadian Press and update the database accordingly.
     * New records will be inserted in the DDB, older ones will be removed, as well as their corresponding media if present
     */
    public function refresh() {
        // Start by getting all the subjects to parse
        $newsSubjects = NewsSubject::all();

        // Get the Canadian Press Storage
        $cpStorage = Storage::disk('canadian-press');

        $parsedArticles = [];

        // Refresh the list of article for each subject
        foreach($newsSubjects as $subject) {
            // Get all the records for this subject
            $subjectRecords = $subject->records()->get();
            $insertedRecords = [];

            // Get all the files in the canadian-press subject directory
            $cpFiles = $cpStorage->files($subject->slug);

            // Filter to only get articles (XML Files)
            $cpArticles = array_filter($cpFiles, function ($item) { return strpos($item, '.xml'); });

            // Parse each article, insert/update it in the database and copy its image if it exist and isn't already stored
            foreach ($cpArticles as $article) {
                // Parse the xml file
                try {
                    $articleXML = simplexml_load_string($cpStorage->get($article));
                }
                catch (Exception $exception) {
                    continue;
                }
                $articleInfos = [
                    'cp_id' => (string)$articleXML->xpath('//doc-id/@id-string')[0],
                    'date' => (string)$articleXML->xpath('//story.date/@norm')[0],
                    'headline' => (string)$articleXML->xpath('//hl1')[0],
                    'media' => $articleXML->xpath('//media-reference/@source'),
                    'subject' => $subject->id,
                    'locale' => $subject->locale,
                ];

                // Parse and reformat the date
                $articleInfos['date'] = date('Y-m-d G:i:s', strtotime($articleInfos['date']));

                // Select the image if there is multiple ones, and check its availability
                if(count($articleInfos['media']) > 0) {
                    $mediaName = $subject->slug.'/'.((string)$articleInfos['media'][0]);
                    $articleInfos['media'] = in_array($mediaName, $cpFiles) ? $mediaName : null;
                } else {
                    $articleInfos['media'] = null;
                }

                // Insert/Update the article in the DDB
                $record = NewsRecord::updateOrCreate(
                    [
                        'cp_id' => $articleInfos['cp_id'],
                        'subject' => $articleInfos['subject']
                    ],
                    $articleInfos
                );

                // If there's an image and it doesn't already exist, we copy it
                if($articleInfos['media'] && !Storage::disk('public')->exists(self::MEDIA_FOLDER.$articleInfos['media'])) {
                    $publicDisk = Storage::disk('public');
                    // The file doesn't exist on our server, let's copy it from the FTP
                    $publicDisk->writeStream(
                        self::MEDIA_FOLDER.$articleInfos['media'],
                        $cpStorage->readStream($articleInfos['media'])
                    );

                    // Get and store the media dimensions
                    $contents = $publicDisk->get(self::MEDIA_FOLDER.$articleInfos['media']);
                    $im = imagecreatefromstring($contents);
                    $width = imagesx($im) ?? null;
                    $height = imagesy($im) ?? null;

                    $record->media_width = $width;
                    $record->media_height = $height;
                    $record->save();

                }

                // Register that this record is live
                array_push($insertedRecords, $record->id);
                array_push($parsedArticles, $article);
            }

            // All articles on the FTP have now been treated. We now need to address articles that are no longer here
            foreach ($subjectRecords as $record) {
                if(in_array($record->id, $insertedRecords)) {g
                    // Record is OK
                    continue;
                }

                // Record is no longer on the FTP, remove it.
                // If it as a media, remove it also
                if ($record->media != null && Storage::disk('public')->exists(self::MEDIA_FOLDER.$record->media)) {
                    Storage::disk('public')->delete(self::MEDIA_FOLDER.$record->media);
                }

                $record->delete();
            }
        }

        return new Response(["parsedAticles" => $parsedArticles]);
    }

    public function categories() : Response {
        return new Response(NewsCategory::orderBy("name")->get());
    }

    public function records(NewsCategory $category) : Response {
        return new Response($category->subjects()->with('records')->get());
    }
}
