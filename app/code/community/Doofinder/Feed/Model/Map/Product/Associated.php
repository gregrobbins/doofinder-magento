<?php
class Doofinder_Feed_Model_Map_Product_Associated
    extends Doofinder_Feed_Model_Map_Product_Abstract
{
    protected function mapField($column)
    {
        $value = parent::mapField($column);

        if ($value == "")
            $value = $this->getParentMap()->mapField($column);

        return $value;
    }

    public function mapFieldDescription($params = array())
    {
        $value = $this->getCellValue(array('map' => $params['map']));

        if ($value == "")
            $value = $this->getParentMap()->mapField('description');

        return $value;
    }

    public function mapFieldLink($params = array())
    {
        $product = $this->getProduct();

        if ($product->isVisibleInSiteVisibility())
        {
            $value = $this->getCellValue(array('map' => $params['map']));
        }
        else
        {
            $value = $this->getParentMap()->mapField('link');

            if ($this->getConfigVar('associated_products_link_add_unique', 'columns'))
                $value = $this->addUrlUniqueParams(
                    $value,
                    $product,
                    $this->getParentMap()->getConfigurableAttributeCodes()
                );
        }

        return $value;
    }

    protected function addUrlUniqueParams($value, $product, $codes)
    {
        $params = array();

        foreach ($codes as $attrCode)
        {
            $data = $product->getData($attrCode);

            if (empty($data))
            {
                $this->skip = true;
                return $value;
            }

            $params[$attrCode] = $data;
        }

        $urlinfo = parse_url($value);

        if ($urlinfo !== false)
        {
            if (isset($urlinfo['query']))
                $urlinfo['query'] .= '&'.http_build_query($params);
            else
                $urlinfo['query'] = http_build_query($params);

            $new = "";

            foreach ($urlinfo as $k => $v)
            {
                if ($k == 'scheme')
                    $new .= $v.'://';
                elseif ($k == 'port')
                    $new .= ':'.$v;
                elseif ($k == 'query')
                    $new .= '?'.$v;
                else
                    $new .= $v;
            }

            if (parse_url($new) === false)
                $this->skip = true;
            else
                $value = $new;
        }

        return $value;
    }

    public function mapFieldImageLink($params = array())
    {
        $value = $this->getCellValue(array('map' => $params['map']));

        if ($value == '')
            $value = $this->getParentMap()->mapField('image_link');

        return $value;
    }

    public function mapFieldAvailability($params = array())
    {
        $args = array('map' => $params['map']);
        $value = "";
        $value = $this->getParentMap()->mapField('availability');
        // gets out of stock if parent is out of stock
        if (strcasecmp($this->getConfig()->getOutOfStockStatus(), $value) == 0)
            return $value;

        $value = $this->getCellValue($args);

        return $value;
    }

    public function mapFieldBrand($params = array())
    {
        $args = array('map' => $params['map']);
        $value = "";

        // get value from parent first
        $value = $this->getParentMap()->mapField('brand');
        if ($value != "")
            return $value;

        $value = $this->getCellValue($args);

        return $value;
    }

    public function mapFieldProductType($params = array())
    {
        $args = array('map' => $params['map']);
        $value = "";

        // get value from parent first
        $value = $this->getParentMap()->mapField('product_type');
        if ($value != "")
            return html_entity_decode($value);

        $map_by_category = $this->getConfig()->getMapCategorySorted('product_type_by_category', $this->getStoreId());
        $category_ids = $this->getProduct()->getCategoryIds();
        if (empty($category_ids))
            $category_ids = $this->getParentMap()->getProduct()->getCategoryIds();
        if (!empty($category_ids) && count($map_by_category) > 0)
        {
            foreach ($map_by_category as $arr)
            {
                if (array_search($arr['category'], $category_ids) !== false)
                {
                    $value = $arr['value'];
                    break;
                }
            }
        }

        if ($value != "")
            return html_entity_decode($value);

        $value = $this->getCellValue($args);

        return html_entity_decode($value);
    }
}
