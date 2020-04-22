<?php

function getStorageUrl($value) {
    return \Illuminate\Support\Facades\Storage::disk(config('filesystems.default'))->url($value);
}
