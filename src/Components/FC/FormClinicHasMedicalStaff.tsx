import React from 'react';
import Translator from '@hubleto/react-ui/core/Translator';
import Form, { FormMetaContext } from '@hubleto/react-ui/components/fc/Form';
import { type FormProps } from '@hubleto/react-ui/components/fc/FormInterfaces';
import Input from '@hubleto/react-ui/components/fc/FormComponents/Input';

const componentName = 'FormClinicHasMedicalStaff';
const parentApp = 'Hubleto/App/External/WaiBlue/HlthcrCore';
const T = new Translator(parentApp + '/Loader', 'Components/' + componentName);

// Added in the clinic's tab, the clinic is given (both legacy apps).
const TabDefault = () => {
  const form = React.useContext(FormMetaContext);
  const isClinicGiven = Number(form.description?.defaultValues?.id_clinic ?? 0) > 0;
  return <div className='grid grid-cols-1 gap-2'>
    <Input field='id_clinic' readonly={isClinicGiven} />
    <Input field='id_user' />
  </div>;
}

const FormClinicHasMedicalStaff = (props: FormProps) => <Form
  componentName={componentName}
  parentApp={parentApp}
  model={parentApp + '/Models/ClinicHasMedicalStaff'}
  urlSlug='hlthcr/clinic-has-medical-staff'
  title={{ sub: T.translate('Medical staff of the clinic')}}
  tabs={{default: {content: () => <TabDefault />}}}
  {...props}
/>

export default FormClinicHasMedicalStaff;
