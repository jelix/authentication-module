<div>
    <h1>{@auth.form.title@}</h1>

    {@auth.form.text.html@}

    {formfull $form,'authloginpass~password_reset:send', array()}

    <p><a href="{jurl 'authloginpass~sign:in'}">{@auth.cancel.and.back.to.login@}</a></p>
</div>