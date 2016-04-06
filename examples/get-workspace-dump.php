<?php

require_once('../asana.php');

// See class comments and Asana API for full info
$asana = new Asana(array('apiKey' => 'XXXXXXXXXXXXX')); // Your API Key, you can get it in Asana

$workspaceId = 42; // The workspace to dump to JSON

// Get all projects in the current workspace (all non-archived projects)
$asana->getProjectsInWorkspace($workspaceId, $archived = false);

// As Asana API documentation says, when response is successful, we receive a 200 in response so...
if ($asana->hasError()) {
    echo 'Error while trying to connect to Asana, response code: ' . $asana->responseCode;
    exit;
}

$projects = $asana->getData();

foreach ($projects as $project) {
    echo '<strong>[ ' . $project->name . ' (id ' . $project->id . ')' . ' ]</strong><br>' . PHP_EOL;
    //if ($project->id != 42) { // Quickly filter on a project
    //  continue;
    //}

    // Get all tasks in the current project
    $asana->getProjectTasks($project->id);
    if ($asana->hasError()) {
        echo 'Error while trying to connect to Asana, response code: ' . $asana->responseCode;
        continue;
    }
    foreach ($asana->getData() as $task) {
        echo '+ ' . $task->name . ' (id ' . $task->id . ')' . ' ]<br>' . PHP_EOL;

        $asana->getTask($task->id);
        if(!$asana->hasError()){
            $task->details = $asana->getData();
            //var_dump($task->details);
        }

        $asana->getTaskStories($task->id);
        if(!$asana->hasError()){
            $task->stories = $asana->getData();
            //var_dump($task->stories);
        }
        $asana->getTaskAttachments($task->id);
        if(!$asana->hasError()){
          $aa = $asana->getData();
          foreach ($aa as $attachment) {
            $asana->getAttachment($attachment->id);
            $attachment2 = $asana->getData();

            // Download.
            $ch = curl_init($attachment2->download_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $data = curl_exec($ch);
            curl_close($ch);

            // TODO: name might be duplicated
            file_put_contents('assets/' . $attachment2->name, $data);
            $task->attachments[] = $attachment2;
          }
        }
    }
}

//var_dump($projects);

echo "All as JSON:\n";
echo json_encode($projects);
