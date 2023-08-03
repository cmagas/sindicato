/*!
 * Ext JS Library 3.3.1
 * Copyright(c) 2006-2010 Sencha Inc.
 * licensing@sencha.com
 * http://www.sencha.com/license
 */
/**
 * @class Ext.calendar.EventEditForm
 * @extends Ext.form.FormPanel
 * <p>A custom form used for detailed editing of events.</p>
 * <p>This is pretty much a standard form that is simply pre-configured for the options needed by the
 * calendar components. It is also configured to automatically bind records of type {@link Ext.calendar.EventRecord}
 * to and from the form.</p>
 * <p>This form also provides custom events specific to the calendar so that other calendar components can be easily
 * notified when an event has been edited via this component.</p>
 * <p>The default configs are as follows:</p><pre><code>
    labelWidth: 65,
    title: 'Event Form',
    titleTextAdd: 'Add Event',
    titleTextEdit: 'Edit Event',
    bodyStyle: 'background:transparent;padding:20px 20px 10px;',
    border: false,
    buttonAlign: 'center',
    autoHeight: true,
    cls: 'ext-evt-edit-form',
</code></pre>
 * @constructor
 * @param {Object} config The config object
 */
Ext.calendar.EventEditForm = Ext.extend(Ext.form.FormPanel, {
    labelWidth: 65,
    title: 'Event Form',
    titleTextAdd: 'Registrar Evento',
    titleTextEdit: 'Editar Evento',
    bodyStyle: 'background:transparent;padding:20px 20px 10px;',
    border: false,
    buttonAlign: 'center',
    autoHeight: true,
    // to allow for the notes field to autogrow
    cls: 'ext-evt-edit-form',

    // private properties:
    newId: 10000,
    layout: 'column',

    // private
    initComponent: function() {

        this.addEvents({
            /**
             * @event eventadd
             * Fires after a new event is added
             * @param {Ext.calendar.EventEditForm} this
             * @param {Ext.calendar.EventRecord} rec The new {@link Ext.calendar.EventRecord record} that was added
             */
            eventadd: true,
            /**
             * @event eventupdate
             * Fires after an existing event is updated
             * @param {Ext.calendar.EventEditForm} this
             * @param {Ext.calendar.EventRecord} rec The new {@link Ext.calendar.EventRecord record} that was updated
             */
            eventupdate: true,
            /**
             * @event eventdelete
             * Fires after an event is deleted
             * @param {Ext.calendar.EventEditForm} this
             * @param {Ext.calendar.EventRecord} rec The new {@link Ext.calendar.EventRecord record} that was deleted
             */
            eventdelete: true,
            /**
             * @event eventcancel
             * Fires after an event add/edit operation is canceled by the user and no store update took place
             * @param {Ext.calendar.EventEditForm} this
             * @param {Ext.calendar.EventRecord} rec The new {@link Ext.calendar.EventRecord record} that was canceled
             */
            eventcancel: true
        });

        this.titleField = new Ext.form.TextField({
			
            fieldLabel: 'T&iacute;tulo',
            name: Ext.calendar.EventMappings.Title.name,
            anchor: '90%'
        });
        this.dateRangeField = new Ext.calendar.DateRangeField({
            fieldLabel: 'Donde',
            anchor: '90%'
        });
        this.reminderField = new Ext.calendar.ReminderField({
            name: 'Reminder'
        });
        this.notesField = new Ext.form.TextArea({
            fieldLabel: 'Notas',
            name: Ext.calendar.EventMappings.Notes.name,
            grow: true,
            growMax: 150,
            anchor: '100%'
        });
        
		
		var almacen=new Ext.data.SimpleStore	(
													{
														fields:	[
																	{name:'id'},
																	{name:'nombre'},
																	{name:'sede'},
																	{name:'ubicacion'}
																	
																]
													}
											)
	if(typeof(arrSedes)!='undefined')											
		almacen.loadData(arrSedes);											
	var comboTmp=document.createElement('select');
	
	this.locationField =new Ext.form.ComboBox	(
													{
														mode:'local',
														emptyText:'Elija una opci\u00f3n',
														store:almacen,
														displayField:'nombre',
														valueField:'id',
														transform:comboTmp,
														editable:false,
														typeAhead: true,
														triggerAction: 'all',
														lazyRender:true,
														anchor:'100%',
														name: Ext.calendar.EventMappings.Location.name,
														fieldLabel: 'Lugar',
														tpl:'<tpl for="."><div class="x-combo-list-item"><span class="letraRojaSubrayada8">Instituci&oacute;n:</span> <span class="corpo8_bold">{nombre}</span><br><span class="letraExt"><b>Zona:</b></span> <span class="copyrigthSinPadding">{sede}</span><br><span class="letraExt"><b>Ubicaci&oacute;n:</b></span><span class="copyrigthSinPadding"> <span style:"font-size:9px !important">({ubicacion})</span></font></span></div></tpl>'
														
													}
												)
	
	

		
        this.urlField = new Ext.form.TextField({
            fieldLabel: 'Enlace Web',
            name: Ext.calendar.EventMappings.Url.name,
            anchor: '100%'
        });

		var gridUsuarios=null;
		if(typeof(crearGridUsuariosInvitados)!='undefined')
			crearGridUsuariosInvitados('gridUsuarios2');
		
		
        var leftFields;
		if(gridUsuarios!=null)
			leftFields = [this.titleField, this.dateRangeField, this.reminderField,gridUsuarios];
		else
			leftFields = [this.titleField, this.dateRangeField, this.reminderField];
        rightFields = [this.notesField, this.locationField, this.urlField];

        if (this.calendarStore) {
            this.calendarField = new Ext.calendar.CalendarPicker({
                store: this.calendarStore,
                name: Ext.calendar.EventMappings.CalendarId.name
            });
            leftFields.splice(2, 0, this.calendarField);
        };

        this.items = [{
            id: 'left-col',
            columnWidth: 0.65,
            layout: 'form',
            border: false,
            items: leftFields
        },
        {
            id: 'right-col',
            columnWidth: 0.35,
            layout: 'form',
            border: false,
            items: rightFields
        }];

        this.fbar = [{
			id:'btnGuardar',
            text: 'Guardar',
            scope: this,
            handler: this.onSave
        },
        {
			id:'btnEliminar',
            cls: 'ext-del-btn',
            text: 'Eliminar',
            scope: this,
            handler: this.onDelete
        },
        {
            text: 'Cancelar',
            scope: this,
            handler: this.onCancel
        }];

        Ext.calendar.EventEditForm.superclass.initComponent.call(this);
    },

    // inherited docs
    loadRecord: function(rec) 
	{

        this.form.loadRecord.apply(this.form, arguments);
        this.activeRecord = rec;
        this.dateRangeField.setValue(rec.data);
        if (this.calendarStore) 
		{
            this.form.setValues({
									'calendar': rec.data[Ext.calendar.EventMappings.CalendarId.name]
								});
        }
		
		
		
        this.isAdd = !!rec.data[Ext.calendar.EventMappings.IsNew.name];
        if (this.isAdd) {
            rec.markDirty();
            this.setTitle(this.titleTextAdd);
            Ext.select('.ext-del-btn').setDisplayed(false);
        }
        else {
            this.setTitle(this.titleTextEdit);
            Ext.select('.ext-del-btn').setDisplayed(true);
        }
        this.titleField.focus();
    },

    // inherited docs
    updateRecord: function() {
        var dates = this.dateRangeField.getValue();

        this.form.updateRecord(this.activeRecord);
        this.activeRecord.set(Ext.calendar.EventMappings.StartDate.name, dates[0]);
        this.activeRecord.set(Ext.calendar.EventMappings.EndDate.name, dates[1]);
        this.activeRecord.set(Ext.calendar.EventMappings.IsAllDay.name, dates[2]);
    },

    // private
    onCancel: function() {
        this.cleanup(true);
        this.fireEvent('eventcancel', this, this.activeRecord);
    },

    // private
    cleanup: function(hide) {
        if (this.activeRecord && this.activeRecord.dirty) {
            this.activeRecord.reject();
        }
        delete this.activeRecord;

        if (this.form.isDirty()) {
            this.form.reset();
        }
    },

    // private
    onSave: function() 
	{

		if(this.titleField.getValue()=='')
		{
			function resp()
			{
				this.titleField.focus();	
			}
			msgBox('El t&iacute;tulo del evento no puede ser vac&iacute;o',resp)
			return;	
		}
		
		if(this.locationField.getValue()=='')
		{
			function resp()
			{
				this.locationField.focus();	
			}
			msgBox('Debe indicar el lugar del evento',resp)
			return;	
		}
		
        if (!this.form.isValid()) 
		{
            return;
        }
        this.updateRecord();

        /*if (!this.activeRecord.dirty) 
		{
            this.onCancel();
            return;
        }*/
		this.activeRecord.data.RO='0';
        this.fireEvent(this.isAdd ? 'eventadd': 'eventupdate', this, this.activeRecord);
    },

    // private
    onDelete: function() {
        this.fireEvent('eventdelete', this, this.activeRecord);
    }
});

Ext.reg('eventeditform', Ext.calendar.EventEditForm);
