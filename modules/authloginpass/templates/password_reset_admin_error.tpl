<div>
    <h1>{@auth.form.change.title@}</h1>
    <p>{@auth.form.change.error.admin.$error_status@}</p>
    <p><a href="{jurl 'jauthdb_admin~default:view', array('j_user_login'=>$login)}">{@auth.admin.form.back.to.account@} {$login}</a></p>
</div>