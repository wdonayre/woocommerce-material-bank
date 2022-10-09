<?php

namespace WooMaterialBank\Interfaces;

interface InterfaceModule{
    public function shortcode($attr);
    public function getShortcodeName();

    public function renderAdminOption($container);
}