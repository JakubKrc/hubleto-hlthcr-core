import App from '@hubleto/react-ui/core/App'

import TableHazards from './Components/FC/TableHazards'
import TableClinics from './Components/FC/TableClinics'
import TableCars from './Components/FC/TableCars'
import TableClinicHasMedicalStaff from './Components/FC/TableClinicHasMedicalStaff'
import ProtectedFileInput from './Components/FC/ProtectedFileInput'

class HlthcrCoreApp extends App {
  init() {
    super.init();
    globalThis.hubleto.registerReactComponent('HlthcrCoreTableHazards', TableHazards);
    globalThis.hubleto.registerReactComponent('HlthcrCoreTableClinics', TableClinics);
    globalThis.hubleto.registerReactComponent('HlthcrCoreTableCars', TableCars);
    globalThis.hubleto.registerReactComponent('HlthcrCoreTableClinicHasMedicalStaff', TableClinicHasMedicalStaff);

    globalThis.hubleto.registerReactComponent('HlthcrCoreProtectedFileInput', ProtectedFileInput);

    // For Hubleto inputs the Hlthcr apps do not use (e.g. the customer's "Manažér").
    globalThis.hubleto.registerReactComponent('HlthcrCoreNoInput', () => null);
  }
}

globalThis.hubleto.registerApp('Hubleto/App/External/WaiBlue/HlthcrCore', new HlthcrCoreApp());
