{if $ratepayValidateIsB2B == 'true'}
    {if $ratepayValidateUST != 'true'}
        <p class='none'>
            <label for='ratepay_ustid'>{s namespace=RatePAY name=vatId}Umsatzsteuer{/s}:</label>
            <input id='ratepay_ustid' class='text' type="text">
        </p>
    {/if}
    {if $ratepayValidateCompanyName != 'true'}
        <p class='none'>
            <label for='ratepay_company'>{s namespace=RatePAY name=company}Firmenname{/s}:</label>
            <input id='ratepay_company' class='text' type="text">
        </p>
    {/if}
{/if}
{if $ratepayValidateTelephoneNumber != 'true'}
    <p class='none'>
        <label for='ratepay_phone'>{s namespace=RatePAY name=phone}Telefonnummer{/s}:</label>
        <input id='ratepay_phone' class='text' type="text">
    </p>
{/if}
{if $ratepayValidateIsBirthdayValid != 'true' || $ratepayValidateisAgeValid != 'true'}
    <p class='none'>
        <label for='ratepay_birthday'>{s namespace=RatePAY name=birthday}Geburtsdatum{/s}:</label>
        <input id='ratepay_birthday' class='text' type="text" value="{$sUserData.billingaddress.birthday}" readonly>
    </p>
{/if}

<script language='javascript'>
    $(document).ready(function() {
    {if $ratepayValidateIsBirthdayValid != 'true' || $ratepayValidateisAgeValid != 'true'}
        $("#ratepay_birthday").datepicker(
            {
                dateFormat: "yy-mm-dd",
                changeYear: true,
                changeMonth: true,
                yearRange: "c-100:c"
            }
        );
    {/if}

    $("#basketButton").click(function(){
        var requestParams = 'userid=' + "{$sUserData.billingaddress.userID}";
        var userUpdate = false;
        $('input[id^="ratepay_"]').each(function(){
            userUpdate = true;
            requestParams += '&'+$(this).attr('id') +'='+ $(this).val();
        });
        if(userUpdate){
            $.ajax({
                type: "POST",
                async: false,
                url: "{url controller='PigmbhRatepay' action='saveUserData'}",
                data: requestParams
            }).done(function( msg ) {
                if(msg == 'OK'){
                    console.log('{s namespace=RatePAY name=updateUserSuccess}UserDaten erfolgreich aktualisiert.{/s}');
                }else{
                    console.log('{s namespace=RatePAY name=updateUserSuccess}Fehler beim Aktualisieren der UserDaten. Return: {/s}' + msg);
                }
            });
        }
    });
});
</script>