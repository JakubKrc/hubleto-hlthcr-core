import React from 'react';
import Translator from '@hubleto/react-ui/core/Translator';
import Form, { FormMetaContext } from '@hubleto/react-ui/components/fc/Form';
import { type FormProps } from '@hubleto/react-ui/components/fc/FormInterfaces';
import Input from '@hubleto/react-ui/components/fc/FormComponents/Input';
import TableClinicHasMedicalStaff from './TableClinicHasMedicalStaff';

const componentName = 'FormClinic';
const parentApp = 'Hubleto/App/External/WaiBlue/HlthcrCore';
const T = new Translator(parentApp + '/Loader', 'Components/' + componentName);

// Medical staff users of this clinic: they see and plan only its exams.
const TabMedicalStaff = () => {
  const form = React.useContext(FormMetaContext);
  if (form.id <= 0) return <div className='alert alert-info'>{T.translate('Save the clinic first.')}</div>;
  return <TableClinicHasMedicalStaff uid={form.uid + '_table_medical_staff'} parentForm={form} idClinic={form.id} />;
}

const FormClinic = (props: FormProps) => <Form
  componentName={componentName}
  parentApp={parentApp}
  model={parentApp + '/Models/Clinic'}
  urlSlug='hlthcr/clinics'
  title={{field: 'name', sub: T.translate('Clinic')}}
  tabs={{
    default: {title: <b>{T.translate('Clinic')}</b>, content: () => <div className='grid grid-cols-1 md:grid-cols-2 gap-2'>
      <Input field='name' />
      <Input field='type' />
      <Input field='street' />
      <Input field='city' />
      <Input field='postal_code' />
      <Input field='contact_person' />
      <Input field='phone' />
      <Input field='email' />
      <Input field='note' />
    </div>},
    medicalStaff: {title: T.translate('Medical staff'), content: () => <TabMedicalStaff />},
  }}
  {...props}
/>

export default FormClinic;
