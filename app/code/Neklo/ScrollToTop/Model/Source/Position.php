<?php
namespace Neklo\ScrollToTop\Model\Source;

class Position implements \Magento\Framework\Option\ArrayInterface
{

    const LEFT_CODE = 'left';
    const LEFT_LABEL = 'Left';

    const RIGHT_CODE = 'right';
    const RIGHT_LABEL = 'Right';

    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::LEFT_CODE,
                'label' => __(self::LEFT_LABEL),
            ),
            array(
                'value' => self::RIGHT_CODE,
                'label' => __(self::RIGHT_LABEL),
            ),
        );
    }

    public function toArray()
    {
        return array(
            self::LEFT_CODE => __(self::LEFT_LABEL),
            self::RIGHT_CODE => __(self::RIGHT_LABEL),
        );
    }

}