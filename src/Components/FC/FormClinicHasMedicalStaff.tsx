import Translator from '@hubleto/react-ui/core/Translator';
import Form from '@hubleto/react-ui/components/fc/Form';
import { type FormProps } from '@hubleto/react-ui/components/fc/FormInterfaces';
import Input from '@hubleto/react-ui/components/fc/FormComponents/Input';

const componentName = 'FormClinicHasMedicalStaff';
const parentApp = 'Hubleto/App/External/WaiBlue/HlthcrCore';
const T = new Translator(parentApp + '/Loader', 'Components/' + componentName);

const FormClinicHasMedicalStaff = (props: FormProps) => <Form
  componentName={componentName}
  parentApp={parentApp}
  model={parentApp + '/Models/ClinicHasMedicalStaff'}
  urlSlug='hlthcr/clinic-has-medical-staff'
  title={{ sub: T.translate('Medical staff of the clinic')}}
  tabs={{default: {content: () => <div className='grid grid-cols-1 gap-2'>
    <Input field='id_clinic' />
    <Input field='id_user' />
  </div>}}}
  {...props}
/>

export default FormClinicHasMedicalStaff;
