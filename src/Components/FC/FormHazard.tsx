import Translator from '@hubleto/react-ui/core/Translator';
import Form from '@hubleto/react-ui/components/fc/Form';
import { type FormProps } from '@hubleto/react-ui/components/fc/FormInterfaces';
import Input from '@hubleto/react-ui/components/fc/FormComponents/Input';

const componentName = 'FormHazard';
const parentApp = 'Hubleto/App/External/WaiBlue/HlthcrCore';
const T = new Translator(parentApp + '/Loader', 'Components/' + componentName);

const FormHazard = (props: FormProps) => <Form
  componentName={componentName}
  parentApp={parentApp}
  model={parentApp + '/Models/Hazard'}
  urlSlug='hlthcr/hazards'
  title={{field: 'name', sub: T.translate('Hazard')}}
  tabs={{
    default: {title: <b>{T.translate('Hazard')}</b>, content: () => <div className='grid grid-cols-1 md:grid-cols-2 gap-2'>
      <Input field='name' />
      <Input field='type' />
    </div>},
    intervals: {title: T.translate('Exam intervals'), content: () => <div className='grid grid-cols-1 md:grid-cols-2 gap-2'>
      <Input field='interval_months' />
      <Input field='interval_months_category_1' />
      <Input field='interval_months_category_2' />
      <Input field='interval_months_category_3' />
      <Input field='interval_months_category_4' />
    </div>},
  }}
  {...props}
/>

export default FormHazard;
