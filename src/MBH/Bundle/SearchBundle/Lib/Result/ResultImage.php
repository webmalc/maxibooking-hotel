<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


class ResultImage
{
    private $isMain;

    private $src;

    private $thumb;

    /**
     * @return mixed
     */
    public function getIsMain()
    {
        return $this->isMain;
    }

    /**
     * @param mixed $isMain
     * @return ResultImage
     */
    public function setIsMain($isMain)
    {
        $this->isMain = $isMain;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSrc()
    {
        return $this->src;
    }

    /**
     * @param mixed $src
     * @return ResultImage
     */
    public function setSrc($src)
    {
        $this->src = $src;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getThumb()
    {
        return $this->thumb;
    }

    /**
     * @param mixed $thumb
     * @return ResultImage
     */
    public function setThumb($thumb)
    {
        $this->thumb = $thumb;

        return $this;
    }


}