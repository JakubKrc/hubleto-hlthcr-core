import Table from '@hubleto/react-ui/components/fc/Table'
import { type TableMeta, type TableProps } from '@hubleto/react-ui/components/fc/TableInterfaces'
import FormHazard from './FormHazard'

const componentName = 'TableHazards'
const parentApp = 'Hubleto/App/External/WaiBlue/HlthcrCore'

const TableHazards = (props: TableProps) => <Table
  componentName={componentName}
  parentApp={parentApp}
  model={parentApp + '/Models/Hazard'}
  baseUrlSlug='hlthcr/hazards'
  formModalProps={{type: 'right wide'}}
  renderForm={(table: TableMeta) => <FormHazard {...table.getDefaultFormProps()} />}
  {...props}
/>;

export default TableHazards;
