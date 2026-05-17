<?php

return [
    "SYNC_TARGETS" => env("GITHUB_SYNC_TARGETS", "[]"),
    "SYNC_OWNER" => env("GITHUB_SYNC_OWNER"),
    "SYNC_REPO" => env("GITHUB_SYNC_REPO"),
    "SYNC_PATH" => env("GITHUB_SYNC_PATH", "hijris.json"),
    "SYNC_BRANCH" => env("GITHUB_SYNC_BRANCH", "main"),
    "SYNC_TOKEN" => env("GITHUB_SYNC_TOKEN"),
];
