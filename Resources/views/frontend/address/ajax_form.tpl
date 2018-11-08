{extends file="parent:frontend/address/ajax_form.tpl"}

{* Error messages *}
{block name="frontend_address_error_messages"}
    {$smarty.block.parent}

    {include file="frontend/plugins/swag_shipping_restriction/index.tpl"}
{/block}