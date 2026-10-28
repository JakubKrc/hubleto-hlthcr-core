import Table from '@hubleto/react-ui/components/fc/Table'
import { type TableMeta, type TableProps } from '@hubleto/react-ui/components/fc/TableInterfaces'
import FormClinic from './FormClinic'

const componentName = 'TableClinics'
const parentApp = 'Hubleto/App/External/WaiBlue/HlthcrCore'

const TableClinics = (props: TableProps) => <Table
  componentName={componentName}
  parentApp={parentApp}
  model={parentApp + '/Models/Clinic'}
  baseUrlSlug='hlthcr/clinics'
  formModalProps={{type: 'right wide'}}
  renderForm={(table: TableMeta) => <FormClinic {...table.getDefaultFormProps()} />}
  {...props}
/>

export default TableClinics;
