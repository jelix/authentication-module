<div>
    <h1>{@authloginpass~password.form.change.title@}</h1>
{if $error_status}
    <p>{@authloginpass~password.form.change.error.$error_status@}</p>
{else}

    {@authloginpass~password.form.change.text.html@}

    {formfull $form, 'authloginpass~password_reset:save', array('request_id'=>$requestId), 'html', array(
        'plugins' => array(
        'pchg_password' => $passwordWidget
    ))}

{/if}

    <p><a href="{jurl 'authcore~sign:in'}">{@authloginpass~auth.cancel.and.back.to.login@}</a></p>
</div>