{if $ratepayValidateisDebitSet == 'false'}
    <input id='ratepay_debit_updatedebitdata' type='hidden' value='true'>
    <p class='none'>
        <label for='ratepay_debit_accountholder'>{s namespace=RatePAY name=accountHolder}Vor- und Nachname Kontoinhaber{/s}:</label>
        <input id='ratepay_debit_accountholder' value="{$smarty.session.Shopware.RatePAY.bankdata.bankholder}" type='text' class='text'>
    </p>
    <p class='none'>
        <label for='ratepay_debit_accountnumber'>{s namespace=RatePAY name=accountNumber}Kontonummer/IBAN{/s}:</label>
        <input id='ratepay_debit_accountnumber' value="{$smarty.session.Shopware.RatePAY.bankdata.account}" type='text' class='text'>
    </p>
    <p class='none'>
        <label for='ratepay_debit_bankcode'>{s namespace=RatePAY name=bankCode}Bankleitzahl{/s}:</label>
        <input id='ratepay_debit_bankcode' value="{$smarty.session.Shopware.RatePAY.bankdata.bankcode}" type='text' class='text'>
    </p>
    <p class='none'>
        <label for='ratepay_debit_bankname'>{s namespace=RatePAY name=bankName}Kreditinstitut{/s}:</label>
        <input id='ratepay_debit_bankname' value="{$smarty.session.Shopware.RatePAY.bankdata.bankname}" type='text' class='text'>
    </p>

    <script language='javascript'>
        $( document ).ready(function() {

            if( !$(":input#ratepay_debit_accountnumber").val().match(/^\d+$/) ) {
                $(":input#ratepay_debit_bankcode").prop('disabled', true);
                $(":input#ratepay_debit_bankcode").hide();
                $("label[for='ratepay_debit_bankcode']").hide();
            }

            $( ":input#ratepay_debit_accountnumber" ).keyup(function() {
                if( $(":input#ratepay_debit_accountnumber").val().match(/^\d+$/) ) {
                    $(":input#ratepay_debit_bankcode").prop('disabled', false);
                    $(":input#ratepay_debit_bankcode").show();
                    $("label[for='ratepay_debit_bankcode']").show();
                } else {
                    $(":input#ratepay_debit_bankcode").prop('disabled', true);
                    $(":input#ratepay_debit_bankcode").hide();
                    $("label[for='ratepay_debit_bankcode']").hide();
                }
            })
        });
    </script>
{/if}
