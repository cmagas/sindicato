function guardar()
{}

guardar.prototype.GetState = function()
{
	return FCK_TRISTATE_OFF;
}

guardar.prototype.Execute = function()
{
	window.parent.guardarDatos();
}

var btnGuardar=new guardar();

FCKCommands.RegisterCommand('btnGuardar', btnGuardar);
    // Add the button.
var item = new FCKToolbarButton('btnGuardar', 'Guardar');
item.IconPath = FCKPlugins.Items['btnGuardar'].Path + 'guardar.jpg';
FCKToolbarItems.RegisterItem('btnGuardar', item);

