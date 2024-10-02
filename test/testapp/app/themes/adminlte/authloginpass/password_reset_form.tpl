<div>
    <h1>{@authloginpass~password.form.title@}</h1>

    {@authloginpass~password.form.text.html@}

    {formfull $form,'authloginpass~password_reset:send', array(), 'adminlte'}

    <div class="form-actions">
        <a href="{jurl 'authcore~sign:in'}">{@authloginpass~auth.cancel.and.back.to.login@}</a>
    </div>
</div>