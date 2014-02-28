{if $sPayment.name == 'rpayratepayinvoice'}
    {include file='frontend/RatePAYErrorMessage.tpl'}
    {include file='frontend/RatePAYFormElements.tpl'}
    {include file='frontend/RatePAYAGBs.tpl'}
{/if}