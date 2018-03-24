<?php

namespace Zipper;

class Zipper
{
    /**
     * The entry point which needs to be zipped.
     *
     * @var string $entry
     */
    protected $entry;

    /**
     * The destination point where to keep the zip.
     *
     * @var string $dest
     */
    protected $dest;

    /**
     * The zip file name w/o extension.
     *
     * @var string
     */
    protected $fileName;

    /**
     * The excludable files.
     *
     * @var array
     */
    protected $excludes = [
        '.',
        '..',
        '.DS_Store',
        '.git',
        '.gitignore',
        'mix-manifest.json',
        'node_modules',
        'assets',
        'package-lock.json',
        'package.json',
        'webpack.mix.js'
     ];

    /**
     * Instantiate the zipper
     *
     * @param string $entry
     * @param string $dest
     */
    public function __construct($entry, $dest = '')
    {
        $this->entry = $entry;
        $this->dest = $dest;
    }

    /**
     * Zip the directory
     *
     * @return void
     */
    public function make()
    {
        $this->ensureEntry()->setFileName()->ensureDest()->moveItems()->zip()->clear();

        info('Find the released zip in '.$this->dest);
    }

    /**
     * Ensure that the user provides an entry point as a directory.
     *
     * @return \Zipper\Zipper $this
     */
    private function ensureEntry()
    {
        // If no entry point we'll warn and exit.
        if (!is_dir($this->entry)) {
            warning('No such directory!');
        }

        return $this;
    }

    /**
     * Set the zip file name w/o extension.
     *
     * @return \Zipper\Zipper $this
     */
    private function setFileName()
    {
        $entryParts = explode('/', $this->entry);

        $this->fileName = array_pop($entryParts);

        return $this;
    }

    /**
     * Ensure the destination for the zip file.
     *
     * @return \Zipper\Zipper $this
     */
    private function ensureDest()
    {
        if (!$this->dest) {
            // Determine the relative destination path from the entry point
            // and generating temporary directory with the file name.
            $this->dest = $this->entry.'/../temp/'.$this->fileName;

            if (is_dir($this->dest)) {
                if (!isDirEmpty($this->dest)) {
                    warning('The directory: `'.$this->dest.'` is not empty!');
                }
            } else {
                makeDir($this->dest);
            }
        }

        // Now, generate the destination real path.
        $this->dest = trim(runCommand('cd '.$this->dest.' && pwd'));

        return $this;
    }

    /**
     * Move the items to the destination.
     *
     * @return \Zipper\Zipper $this
     */
    private function moveItems()
    {
        $items = $this->scanItems($this->entry);

        foreach ($items as $item) {
            $file = arr_end(explode($this->fileName.'/', $item));

            $dynamicDir = trim(runCommand('dirname '.$file));

            if ($dynamicDir != '.') {
                $command = 'cd '.$this->dest.' && mkdir -p "'.$dynamicDir.'" && cp '.$item.' '.$this->dest.'/'.$file;
            } else {
                $command = 'cp '.$item.' '.$this->dest.'/'.$file;
            }

            runCommand($command);
        }

        return $this;
    }

    /**
     * Scan the items recursively starting from the entry point.
     *
     * @param  string $dir
     * @return array  $result
     */
    private function scanItems($dir)
    {
        $items = scandir($dir);

        $result = [];

        foreach ($items as $item) {
            if (!$this->shouldExclude($item)) {
                if (is_dir($dir.DIRECTORY_SEPARATOR.$item)) {
                    $result = array_merge($result, $this->scanItems($dir.DIRECTORY_SEPARATOR.$item));
                } else {
                    $result[] = $dir.DIRECTORY_SEPARATOR.$item;
                }
            }
        }

        return $result;
    }

    /**
     * Determine if we should exclude the item.
     *
     * @param  string $item
     * @return bool
     */
    private function shouldExclude($item)
    {
        return in_array($item, $this->excludes);
    }

    /**
     * Zip the folder.
     *
     * @return \Zipper\Zipper $this
     */
    private function zip()
    {
        runCommand('cd '.$this->dest.'/../ && zip -r -X '.$this->fileName.'.zip '.$this->fileName);

        return $this;
    }

    /**
     * Clear the temp directory.
     *
     * @return \Zipper\Zipper $this
     */
    private function clear()
    {
        $this->dest = runCommand('cd '.$this->dest.'/../ && mv '.$this->fileName.'.zip ../ && cd ../ && rm -rf temp && pwd');

        return $this;
    }
}
