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
    $(document).ready(function () {
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

        $("#basketButton").click(function () {
            var requestParams = 'userid=' + "{$sUserData.billingaddress.userID}";
            var userUpdate = false;
            var error = false;
            $('input[id^="ratepay_"]').each(function () {
                userUpdate = true;
                requestParams += '&' + $(this).attr('id') + '=' + $(this).val();
                if ($(this).val() == '' || $(this).val() == '0000-00-00') {
                    error = true;
                }

                /* validate sepa direct debit - no error if no blz is net @toDo: fix for international direct debits */

                if ($(this).attr('id') == 'ratepay_debit_bankcode' && !$(":input#ratepay_debit_accountnumber").val().match(/^\d+$/)) {
                    error = false;
                }

            });
            if (error) {
                $("#ratepay_error").append('{s namespace=RatePAY name=invaliddata}Bitte vervollst&auml;ndigen Sie die Daten.{/s}');
                $("#ratepay_error").parent().show();
                $('html, body').animate({
                    scrollTop: $("#ratepay_error").offset().top - 100
                }, 1000);
                return false;
            }

            if (userUpdate) {
                $.ajax({
                    type: "POST",
                    async: false,
                    
                    {if $smarty.server.HTTPS eq "on"}
                        url: "{url controller='RpayRatepay' action='saveUserData' forceSecure}",
                    {else}
                        url: "{url controller='RpayRatepay' action='saveUserData'}",
                    {/if}


                    data: requestParams
                }).done(function (msg) {
                            if (msg == 'OK') {
                                console.log('{s namespace=RatePAY name=updateUserSuccess}UserDaten erfolgreich aktualisiert.{/s}');
                            } else {
                                console.log('{s namespace=RatePAY name=updateUserSuccess}Fehler beim Aktualisieren der UserDaten. Return: {/s}' + msg);
                            }
                        });
            }
        });
    });
</script>
