<?php
$root = $argv[1];
$list = json_decode(file_get_contents($argv[2]), true);
$result = [];
foreach ($list as $name) {
    $parts = [];
    foreach (token_get_all(file_get_contents($root . '/' . $name), TOKEN_PARSE) as $token) {
        if (is_array($token)) {
            if (in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }
            $parts[] = [$token[0], $token[1]];
        } else {
            $parts[] = $token;
        }
    }
    $result[$name] = hash('sha256', serialize($parts));
}
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
