<div>
    <h1>{@authloginpass~password.form.title@}</h1>

    {@authloginpass~password.form.text.html@}

    {formfull $form,'authloginpass~password_reset:send', array()}

    <p><a href="{jurl 'authloginpass~sign:in'}">{@authloginpass~auth.cancel.and.back.to.login@}</a></p>
</div>