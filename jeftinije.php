<?php

    $config = array(
        'domain' => 'https://www.domena.com',
        'base_category' => 'Telefoni',
        'warranty' => '1 godina',
        'curCode' => 'HRK',
        'delivery' => 30,
        'freeDeliveryAfter' => 300,
        'limit' => 10000,
    );

    include('./app/bootstrap.php');

    use Magento\Framework\App\Bootstrap;

    $bootstrap = Bootstrap::create(BP, $_SERVER);

    $objectManager = $bootstrap->getObjectManager();

    $state = $objectManager->get('Magento\Framework\App\State');

    $state->setAreaCode('frontend');

    $media_path = $config['domain'] . '/pub/media/catalog/product';

    $stockState = $objectManager->get('Magento\CatalogInventory\Api\StockStateInterface');

    // GET ALL ENABLED PRODUCTS

    $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');

    $_productCollection = $productCollection->create()
        ->addAttributeToSelect('*')
        ->setPageSize($config['limit'])
        ->addAttributeToFilter(
            'status', array('eq' => 1)
        )
        ->setCurPage(1)
        ->load();
    
    
    // SET XML CONTENT TXPE
    header("Content-type: text/xml");

    // START WITH OUTPUT
    echo "<?xml version='1.0' encoding='UTF-8' ?>\n";

    echo "<CNJExport>\n";

    foreach ($_productCollection as $_product) {

        // PREPARE CATEGORY PATH
        $categories = $_product->getCategoryIds();

        $category_path = $config['base_category'];
        if(isset($categories[0])) {
            $category_path = $config['base_category'] . ' &gt; ';
            $category_path .= $objectManager->create('Magento\Catalog\Model\Category')->load($categories[0])->getName();
        }

        // PREPARE MAIN IMAGE & OTHER IMAGES
        $image_url = '';

        if($_product->getImage()) {
            $image_url = $media_path . $_product->getImage();
        }

        $objectManager->get('Magento\Catalog\Model\Product\Gallery\ReadHandler')->execute($_product);

        $images = $_product->getMediaGalleryImages();

        $other_images = array();

        foreach ($images as $image) {

            $image_file = $image->getData()['file'];

            if($image_file == $_product->getImage()) {
                continue;
            }

            $other_images[] .= $media_path . $image_file;
        }

        // DEFINE DELIVERY PRICE
        $delivery = $config['delivery'];

        // FREE DELIVERY IF AMOUNT LARGER THAN 300
        if($_product->getFinalPrice() > $config['freeDeliveryAfter']) {
            $delivery = '0.00';
        }

        $qty = $stockState->getStockQty($_product->getId(), $_product->getStore()->getWebsiteId());
        $stockText = $qty > 0 ? 'Raspoloživo odmah' : 'Dolazi uskoro';
        $stock = $qty > 0 ? 'in stock' : 'out of stock';

        // ECHO ITEM
        echo "\t<Item>\n";
        echo "\t\t<ID><![CDATA[" . $_product->getSku() . "]]></ID>\n";
        echo "\t\t<name><![CDATA[" . str_replace('&#8211;', '–', $_product->getName()) . "]]></name>\n";
        echo "\t\t<description><![CDATA[" . $_product->getDescription() . "]]></description>\n";
        echo "\t\t<link><![CDATA[" . $_product->getProductUrl() . "]]></link>\n";
        echo "\t\t<mainImage><![CDATA[" . $image_url . "]]></mainImage>\n";
        echo "\t\t<moreImages><![CDATA[" . implode(',', $other_images) . "]]></moreImages>\n";
        echo "\t\t<price>" . number_format($_product->getFinalPrice(), 2) . "</price>\n";
        echo "\t\t<regularPrice>" . number_format($_product->getPriceInfo()->getPrice('regular_price')->getValue(), 2) . "</regularPrice>\n";
        echo "\t\t<curCode>" . $config['curCode'] . "</curCode>\n";
        echo "\t\t<stockText><![CDATA[" . $stockText . "]]></stockText>\n";
        echo "\t\t<stock>" . $stock . "</stock>\n";
        echo "\t\t<quantity>" . $qty . "</quantity>\n";
        echo "\t\t<fileUnder><![CDATA[" . $category_path . "]]></fileUnder>\n";
        echo "\t\t<brand><![CDATA[" . $_product->getAttributeText('manufacturer') . "]]></brand>\n";
        echo "\t\t<EAN></EAN>\n";
        echo "\t\t<productCode><![CDATA[" . $_product->get_sku() . "]]></productCode>\n";
        echo "\t\t<warranty><![CDATA[" . $config['warranty'] . "]]></warranty>\n";
        echo "\t\t<deliveryCost>" . number_format($delivery, 2) . "</deliveryCost>\n";
        echo "\t\t<deliveryTimeMin>1</deliveryTimeMin>\n";
        echo "\t\t<deliveryTimeMax>2</deliveryTimeMax>\n";
        echo "\t</Item>\n";

    }

   echo "</CNJExport>";