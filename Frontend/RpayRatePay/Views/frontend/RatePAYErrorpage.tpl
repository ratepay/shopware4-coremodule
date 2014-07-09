{extends file="frontend/detail/index.tpl"}
{block name="frontend_index_content"}
    <div>
        <div>
            <p style="padding:10px;width:50%;display: block;position:absolute;left:25%;top:50%" class="center">
                <span style="color: #999;">
                    {s namespace=RatePAY name=errorpagetext}
                        Leider ist eine Bezahlung mit RatePAY nicht möglich. Diese Entscheidung ist auf Grundlage einer automatisierten
                        Datenverarbeitung getroffen worden. Einzelheiten hierzu finden Sie in der
                    {/s}
                </span>
                <a href='http://www.ratepay.com/zusaetzliche-geschaeftsbedingungen-und-datenschutzhinweis' target='_blank'>RatePAY-Datenschutzerklärung</a>.
            </p>
        </div>
        <div class="actions">
            <a class="button-left large left" href="{url controller=checkout action=cart}">
                {s namespace=RatePAY name=errorpagecart}Warenkorb anzeigen{/s}
            </a>
            <a class="button-right large right" href="{url controller=account action=payment sTarget=checkout}">
                {s namespace=RatePAY name=errorpagepayment}Zahlart ändern{/s}
            </a>
        </div>
    </div>
{/block}
