{extends file="parent:frontend/checkout/confirm.tpl"}

{block name="frontend_checkout_confirm_information_addresses_equal_panel_billing_invalid_data"}
    {if $invalidShippingCountry}
        {include file='frontend/_includes/messages.tpl' type="warning" content="{s name='CountryNotAvailableForShipping' namespace="frontend/address/index"}{/s}"}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}


{block name="frontend_checkout_confirm_information_addresses_shipping_panel_body_invalid_data"}
    {if $invalidShippingCountry}
        {include file='frontend/_includes/messages.tpl' type="warning" content="{s name='CountryNotAvailableForShipping' namespace="frontend/address/index"}{/s}"}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}