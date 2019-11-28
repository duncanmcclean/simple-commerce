<?php

namespace Damcclean\Commerce\Models;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Statamic\Facades\YAML;
use Symfony\Component\Finder\SplFileInfo;

class BaseModel
{
    public $name;
    public $slug;
    public $route;
    public $primaryColumn;

    public function __construct($path = null)
    {
        $this->filesystem = new Filesystem();

        if ($path == null) {
            $this->path = base_path().'/content/commerce/'.$this->slug;
        } else {
            $this->path = $path;
        }

        if (! file_exists($this->path)) {
            (new Filesystem())->makeDirectory($this->path, 0755, true, true);
        }
    }

    public function attributes($file)
    {
        $attributes = Yaml::parse(file_get_contents($file));
        $attributes['slug'] = isset($attributes['slug']) ? $attributes['slug'] : str_replace('.md', '', basename($file));
        $attributes['edit_url'] = $this->editRoute($attributes['slug']);
        $attributes['delete_url'] = $this->deleteRoute($attributes['slug']);

        return $attributes;
    }

    public function all(array $options = [])
    {
        $files = File::allFiles($this->path);

        return collect($files)
            ->reject(function (SplFileInfo $file) {
                if ($file->getExtension() == 'md') {
                    return false;
                }

                return true;
            })
            ->map(function ($item) {
                return $this->attributes($item);
            })
            ->reject(function ($item) use ($options) {
                if (! array_key_exists('enabled', $item)) {
                    return false;
                }

                if (array_key_exists('showDisabled', $options)) {
                    if ($options['showDisabled'] == true) {
                        return false;
                    }
                }

                return !$item['enabled'];
            });
    }

    public function get(string $slug)
    {
        $path = $this->path.'/'.$slug.'.md';

        return json_decode(collect($this->attributes($path))->toJson());
    }

    public function search($query)
    {
        $everything = $this->all([
            'showDisabled' => true
        ]);

        if (! $query) {
            return $everything;
        }

        return $everything
            ->filter(function ($item) use ($query) {
                return false !== stristr($item["$this->primaryColumn"], $query);
            });
    }

    public function save(string $slug, array $data)
    {
        $contents = Yaml::dumpFrontMatter($data, null);

        file_put_contents($this->path.'/'.$slug.'.md', $contents);

        return collect($data);
    }

    public function update(string $slug, array $data)
    {
        $contents = Yaml::dumpFrontMatter($data, null);

        file_get_contents($this->path.'/'.$slug.'.md', $contents);

        if ($data['slug'] != $slug) {
            $this->filesystem->move($this->path.'/'.$slug.'.md', $this->path.'/'.$data['slug'].'.md');
        }

        return collect($data);
    }

    public function delete(string $slug)
    {
        return $this->filesystem->delete($this->path.'/'.$slug.'.md');
    }

    public function editRoute(string $slug)
    {
        return cp_route("$this->route.edit", [
            "$this->slug" => $slug
        ]);
    }

    public function deleteRoute(string $slug)
    {
        return cp_route("$this->route.destroy", [
            "$this->slug" => $slug
        ]);
    }
}
