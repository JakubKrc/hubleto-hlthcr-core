import Table from '@hubleto/react-ui/components/fc/Table'
import { type TableMeta, type TableProps } from '@hubleto/react-ui/components/fc/TableInterfaces'
import FormClinicHasMedicalStaff from './FormClinicHasMedicalStaff'

interface TableClinicHasMedicalStaffProps extends TableProps {
  idClinic?: number,
}

const componentName = 'TableClinicHasMedicalStaff'
const parentApp = 'Hubleto/App/External/WaiBlue/HlthcrCore'

const TableClinicHasMedicalStaff = (props: TableClinicHasMedicalStaffProps) => {
  const idClinic = props.idClinic ?? 0;
  return <Table
    componentName={componentName}
    parentApp={parentApp}
    model={parentApp + '/Models/ClinicHasMedicalStaff'}
    baseUrlSlug='hlthcr/clinic-has-medical-staff'
    endpointParams={{idClinic: idClinic}}
    formDefaultValues={idClinic > 0 ? {id_clinic: idClinic} : {}}
    formModalProps={{type: 'right'}}
    renderForm={(table: TableMeta) => <FormClinicHasMedicalStaff {...table.getDefaultFormProps()} />}
    {...props}
  />;
}

export default TableClinicHasMedicalStaff;
