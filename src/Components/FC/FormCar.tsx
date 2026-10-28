import Translator from '@hubleto/react-ui/core/Translator';
import Form from '@hubleto/react-ui/components/fc/Form';
import { type FormProps } from '@hubleto/react-ui/components/fc/FormInterfaces';
import Input from '@hubleto/react-ui/components/fc/FormComponents/Input';

const componentName = 'FormCar';
const parentApp = 'Hubleto/App/External/WaiBlue/HlthcrCore';
const T = new Translator(parentApp + '/Loader', 'Components/' + componentName);

const FormCar = (props: FormProps) => <Form
  componentName={componentName}
  parentApp={parentApp}
  model={parentApp + '/Models/Car'}
  urlSlug='hlthcr/cars'
  title={{field: 'name', sub: T.translate('Car')}}
  tabs={{default: {content: () => <div className='grid grid-cols-1 md:grid-cols-2 gap-2'>
    <Input field='name' />
    <Input field='license_plate' />
  </div>}}}
  {...props}
/>

export default FormCar;
