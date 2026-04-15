<?php

namespace Company\Dict;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;
use Company\MVC\RouterFilter;

/**
 * Collection route
 */
$colCtrl = "\\Company\\Dict\\Controller\\CollectionCtrl";
$itemCtrl = "\\Company\\Dict\\Controller\\ItemCtrl";
R::getInstance()->addRoute(new MVC('/rest/collections(/:id)', 'POST,PUT', $colCtrl, "updateCollection", new RouterFilter("collection")));
R::getInstance()->addRoute(new MVC('/rest/collections/:id', 'GET', $colCtrl, "getCollection", new RouterFilter("collection")));
R::getInstance()->addRoute(new MVC('/rest/collections', 'GET', $colCtrl, "getCollections", new RouterFilter("collection")));
R::getInstance()->addRoute(new MVC('/rest/collections/:id', 'DELETE', $colCtrl, "deleteCollection", new RouterFilter("collection")));

/**
 * Item route
 */
R::getInstance()->addRoute(new MVC('/rest/collections/:collectionID/items(/:itemID)', 'POST,PUT', $itemCtrl, "updateItem", new RouterFilter("collection")));
R::getInstance()->addRoute(new MVC('/rest/collections/:collectionID/items', 'GET', $itemCtrl, "getItems", new RouterFilter("collection")));
R::getInstance()->addRoute(new MVC('/rest/collections/:collectionID/items/:itemID', 'GET', $itemCtrl, "getItem", new RouterFilter("collection")));
R::getInstance()->addRoute(new MVC('/rest/collections/:collectionID/items/:itemID', 'DELETE', $itemCtrl, "deleteItem", new RouterFilter("collection")));
