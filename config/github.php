<?php

return [
    "SYNC_MAX_TARGETS" => (int) env("GITHUB_SYNC_MAX_TARGETS", 10),
    "SYNC_DEFAULT_PATH" => env("GITHUB_SYNC_DEFAULT_PATH", "hijris.json"),
    "SYNC_DEFAULT_BRANCH" => env("GITHUB_SYNC_DEFAULT_BRANCH", "main"),
];
