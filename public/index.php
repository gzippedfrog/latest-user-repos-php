<?php

$githubUsers = [
    'rcmaehl',
    'krlvm',
    'torvalds',
    'valinet',
    'WoeUSB',
    'romkatv',
    'AutoDarkMode',
    'black7375',
    'ventoy',
    'flightlessmango'
];

$cacheFile = __DIR__ . '/../reposCache.json';
$cacheDuration = 10 * 60;

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheDuration) {
    $reposCache = json_decode(file_get_contents($cacheFile), true);
    $updatedAt = filemtime($cacheFile);
} else {
    $reposCache = getLatestRepos($githubUsers);
    $updatedAt = time();

    file_put_contents($cacheFile, json_encode($reposCache));
}


function getLatestRepos($githubUsers)
{
    $repos = [];

    foreach ($githubUsers as $user) {
        $url = "https://api.github.com/users/{$user}/repos?sort=updated&per_page=100";

        $options = [
            'http' => [
                'header' => [
                    'User-Agent: PHP',
                ]
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $userRepos = json_decode($response, true);

        if (is_array($userRepos)) {
            $repos = array_merge($repos, $userRepos);
        }
    }

    usort($repos, function ($a, $b) {
        return strtotime($b['updated_at']) - strtotime($a['updated_at']);
    });

    return array_slice($repos, 0, 10);
}

?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>10 самых свежих репозиториев</title>

    <style>
        body {
            background-color: #333;
            color: #aaa;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        h1 {
            text-align: center;
        }

        a {
            color: aquamarine;
        }

        li {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
<h1>
    10 Самых свежих репозиториев <br>
    на <?= date('H:i:s d.m.Y', $updatedAt) ?>
</h1>
<ol>
    <?php foreach ($reposCache as $repo): ?>
        <li>
            <a href="<?= $repo['html_url'] ?>" target="_blank"><?= $repo['full_name'] ?></a><br>
            Обновлен <?= date('H:i:s d.m.Y', strtotime($repo['updated_at'])) ?>
        </li>
    <?php endforeach; ?>
</ol>
</body>

</html>