<div>
    <h1>{@authloginpass~password.form.change.title@}</h1>

    {@authloginpass~password.form.change.text.html@}

    {formfull $form, 'authloginpass~passwordEdit:save', [], 'adminlte', array(
        'plugins' => array(
        'new_password' => 'passwordeditor_html'
    ))}

</div>
