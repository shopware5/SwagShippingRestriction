{extends file="parent:frontend/register/shipping_fieldset.tpl"}

{block name='frontend_register_shipping_fieldset_input_country'}
	<div class="register--shipping-country field--select">
		<select name="register[shipping][country]"
				data-address-type="shipping"
				id="country_shipping"
				required="required"
				aria-required="true"
				class="select--country is--required{if isset($error_flags.country)} has--error{/if}">

			<option value=""
					disabled="disabled"
					selected="selected">
				{s name='RegisterShippingPlaceholderCountry'}{/s}{s name="RequiredField" namespace="frontend/register/index"}{/s}
			</option>

			{foreach from=$country_list item=country}
				{if empty($country.attributes.core) || $country.attributes.core->get('swag_allow_shipping')}
					<option value="{$country.id}"{if $country.id eq $form_data.country} selected="selected"{/if}>
						{$country.countryname}
					</option>
				{/if}
			{/foreach}

		</select>
	</div>
{/block}