<?php

class ModelToolImage extends Model
{
    public function resize($filename, $width, $height)
    {
        if (!is_file($this->{'path.image'}.DIRECTORY_SEPARATOR.$filename)) {
            return;
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $old_image = $filename;
        $new_image = 'cache/'.utf8_substr($filename, 0, utf8_strrpos($filename, '.')).'-'.$width.'x'.$height.'.'.$extension;

        if (!is_file($this->{'path.image'}.DIRECTORY_SEPARATOR.$new_image) || (filectime($this->{'path.image'}.DIRECTORY_SEPARATOR.$old_image) > filectime($this->{'path.image'}.DIRECTORY_SEPARATOR.$new_image))) {
            $path = '';

            $directories = explode('/', dirname(str_replace('../', '', $new_image)));

            foreach ($directories as $directory) {
                $path = $path.'/'.$directory;

                if (!is_dir($this->{'path.image'}.DIRECTORY_SEPARATOR.$path)) {
                    @mkdir($this->{'path.image'}.DIRECTORY_SEPARATOR.$path, 0777);
                }
            }

            list($width_orig, $height_orig) = getimagesize($this->{'path.image'}.DIRECTORY_SEPARATOR.$old_image);

            if ($width_orig != $width || $height_orig != $height) {
                $image = new Image($this->{'path.image'}.DIRECTORY_SEPARATOR.$old_image);
                $image->resize($width, $height);
                $image->save($this->{'path.image'}.DIRECTORY_SEPARATOR.$new_image);
            } else {
                copy($this->{'path.image'}.DIRECTORY_SEPARATOR.$old_image, $this->{'path.image'}.DIRECTORY_SEPARATOR.$new_image);
            }
        }

        if ($this->request->server['HTTPS']) {
            return $this->config->get('config_ssl').'image/'.$new_image;
        } else {
            return $this->config->get('config_url').'image/'.$new_image;
        }
    }
}
