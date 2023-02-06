<?php
    class Tile {
        private $name            = '';
        private $size            = '1x1';
        private $theme           = 'blue';
        private $url             = '';
        private $link            = '';
        private $title           = '';
        private $content         = '';
        private $description     = '';
        private $thumbnail       = '';
        private $colour          = '';
        private $full_background = false;

        public function __construct() {
        }

        public function setName($name) {
            $this->name = $name;
        }

        public function getName() {
            return $this->name;
        }

        public function setSize($size) {
            $this->size = $size;
        }

        public function getSize() {
            return $this->size;
        }

        public function setThumbnail($thumbnail) {
            $this->thumbnail = $thumbnail;
        }

        public function getThumbnail() {
            return $this->thumbnail;
        }

        public function setContent($content) {
            $this->content = $content;
        }

        public function getContent() {
            return $this->content;
        }

        public function setDescription($description) {
            $this->description = $description;
        }

        public function getDescription() {
            return $this->description;
        }

        public function setUrl($url) {
            $this->url = $url;
        }

        public function getUrl() {
            return $this->url;
        }

        public function setLink($link) {
            $this->link = $link;
        }

        public function getLink() {
            return $this->link;
        }

        public function setTheme($theme) {
            $this->theme = $theme;
        }

        public function getTheme() {
            return $this->theme;
        }

        public function setColour($colour) {
            $this->colour = $colour;
        }

        public function getColour() {
            return $this->colour;
        }

        public function useFullBackgroundImage($full_bg = null) {
            if ($full_bg !== null) {
                $this->full_background = $full_bg;
            }

            return $this->full_background;
        }

        public function display() {
            print $this->get();
        }

        public function get() {
            $background_colour = $this->getColour() ? 'background-color:' . $this->getColour() . ';' : '';
            $background_image  = strlen($this->getThumbnail()) ? 'background-image:url(\'' . $this->getThumbnail() . '\');' : '';
            $background_size   = $this->useFullBackgroundImage() ? 'background-size:cover;' : '';

            $html = '
                <div class="widget widget' . $this->getSize() . ' widget_' . $this->getTheme() . ($this->getLink() ? ' widget_link' : '') . ' animation unloaded"' . ($this->getColour() ? ' style="background-color:' . $this->getColour() . ';"' : '') . ' data-url="' . $this->getUrl() . '" data-theme="' . $this->getTheme() . '" data-name="' . $this->getName() . '"' . ($this->getLink() ? ' data-link="' . $this->getLink() . '"' : '') . '>
                    <div class="widget_content">
                        <div class="main"' . ($background_colour || $background_image || $background_size ? ' style="' . $background_colour . $background_image . $background_size . '"' : '') . '>
                            <span>' . $this->getContent() . '</span>
                        </div>
                    </div>
                </div>';

            return $html;
        }
    }
?>