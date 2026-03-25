<?php

/**
 * Local IDE stub for environments where the Imagick extension is not indexed
 * by Intelephense. This file is not autoloaded in runtime.
 */
if (! class_exists('Imagick')) {
    class Imagick implements Iterator
    {
        public function setResolution(float $xResolution, float $yResolution): bool
        {
            return true;
        }

        public function readImage(string $filename): bool
        {
            return true;
        }

        public function setImageFormat(string $format): bool
        {
            return true;
        }

        public function writeImage(?string $filename = null): bool
        {
            return true;
        }

        public function clear(): bool
        {
            return true;
        }

        public function destroy(): bool
        {
            return true;
        }

        public function current(): mixed
        {
            return null;
        }

        public function next(): void {}

        public function key(): mixed
        {
            return null;
        }

        public function valid(): bool
        {
            return false;
        }

        public function rewind(): void {}
    }
}
