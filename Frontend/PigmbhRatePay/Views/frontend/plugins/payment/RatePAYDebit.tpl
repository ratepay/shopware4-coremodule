{if $sPayment.name == 'pigmbhratepaydebit'}
    {include file='frontend/RatePAYErrorMessage.tpl'}
    {include file='frontend/RatePAYFormElements.tpl'}
    {include file='frontend/RatePAYDebitFormElements.tpl'}
    {include file='frontend/RatePAYAGBs.tpl'}
{/if}
