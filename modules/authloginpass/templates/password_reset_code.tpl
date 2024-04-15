<div>
    <h1>{@authloginpass~password.form.resetcode.title@}</h1>
{if $error_status}
    <p>{@authcore~auth.request.confirmation.error.$error_status@}</p>
{else}

    {@authloginpass~password.form.resetcode.text.html@}

    {formfull $form,  'authloginpass~password_reset:checkcode', array('request_id'=>$requestId)}

{/if}
{if $email}
    <p><a href="{jurl 'authloginpass~password_reset:index', array('email'=>$email)}">{@authloginpass~password.form.resetcode.resend.code@}</a></p>
{else}
    <p><a href="{jurl 'authloginpass~password_reset:index'}">{@authloginpass~password.form.resetcode.resend.code@}</a></p>
{/if}
    <p><a href="{jurl 'authcore~sign:in'}">{@authloginpass~auth.cancel.and.back.to.login@}</a></p>
</div>