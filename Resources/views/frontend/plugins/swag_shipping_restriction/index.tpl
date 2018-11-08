{if $notAvailableCountries}
    <div class="address-editor--errors">
        {s name="CountryNotAvailableEditor" namespace="frontend/address/index" assign="sCountryNotAvailable"}{/s}
        {s name="CountryNotAvailableEditorInfo" namespace="frontend/address/index" assign="sCountryNotAvailableInfo"}{/s}

        {$restrictionCountries = []}

        {foreach from=$notAvailableCountries item=country}
            {$restrictionCountries[] = '<li>'|cat:$country:'</li>'}
        {/foreach}

        {$restrictionCountries = ""|implode:$restrictionCountries}
        {$sCountryNotAvailable = $sCountryNotAvailable|replace:'%s':$restrictionCountries}


        {include file="frontend/_includes/messages.tpl" type="warning" content=$sCountryNotAvailable|cat:$sCountryNotAvailableInfo}
    </div>
{/if}