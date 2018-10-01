<?php
namespace Neklo\ScrollToTop\Model\Source;

class Color
{

    protected $_arrayList = array(
        '1bbc9d',
        '2fcc71',
        '3598dc',
        '9c59b8',
        'f49c14',
        'e84c3d',
        'bec3c7',
        '808b8d',
        '16a086',
        '27ae61',
        '2a80b9',
        '8f44ad',
        'fe6b0b',
        'c1392b',
        '96a6a6',
        '262626',
    );

    /**
     * @return array
     */
    public function getColorList()
    {
        return $this->_arrayList;
    }

}