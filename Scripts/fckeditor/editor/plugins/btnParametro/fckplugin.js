function cParametro()
{}

cParametro.prototype.GetState = function()
{
	return FCK_TRISTATE_OFF;
}

cParametro.prototype.Execute = function()
{
	window.parent.insertarParametro_click();
}

var btnParametro=new cParametro();

FCKCommands.RegisterCommand('btnParametro', btnParametro);
    // Add the button.
var item = new FCKToolbarButton('btnParametro', 'Insertar variable de dato');
item.IconPath ='../../../images/page_white_gear.png';
FCKToolbarItems.RegisterItem('btnParametro', item);

