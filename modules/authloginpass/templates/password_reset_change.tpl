<div>
    <h1>{@auth.form.change.title@}</h1>
{if $error_status}
    <p>{@auth.form.change.error.$error_status@}</p>
{else}

    {@auth.form.change.text.html@}

    {formfull $form,'authloginpass~password_reset:save', array()}

{/if}

    <p><a href="{jurl 'authloginpass~login:index'}">{@auth.cancel.and.back.to.login@}</a></p>
</div>