import Table from '@hubleto/react-ui/components/fc/Table'
import { type TableMeta, type TableProps } from '@hubleto/react-ui/components/fc/TableInterfaces'
import FormCar from './FormCar'

const componentName = 'TableCars'
const parentApp = 'Hubleto/App/External/WaiBlue/HlthcrCore'

const TableCars = (props: TableProps) => <Table
  componentName={componentName}
  parentApp={parentApp}
  model={parentApp + '/Models/Car'}
  baseUrlSlug='hlthcr/cars'
  formModalProps={{type: 'right wide'}}
  renderForm={(table: TableMeta) => <FormCar {...table.getDefaultFormProps()} />}
  {...props}
/>

export default TableCars;
