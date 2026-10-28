import React from 'react';
import { FormMetaContext } from '@hubleto/react-ui/components/fc/Form';
import { useRecordField } from '@hubleto/react-ui/components/fc/FormRecordStore';

interface FileDownloadButtonProps {
  urlSlug: string,
  fileField?: string,
  // Last part of the download URL, for records with more than one file (e.g. "scan").
  downloadAction?: string,
  text: string,
  noFileText: string,
}

/**
 * Opens the protected download <urlSlug>/<id>/<downloadAction> of the saved file (the upload folder is not public).
 * The server decides whether the user may open it.
 */
const FileDownloadButton = (props: FileDownloadButtonProps) => {
  const form = React.useContext(FormMetaContext);
  const file = useRecordField<string>(props.fileField ?? 'file', '');

  const isSaved = form.id > 0;
  const hasFile = file !== null && file !== '';
  if (!isSaved) return null;
  if (!hasFile) return <div className='text-gray-500'>{props.noFileText}</div>;

  const downloadAction = props.downloadAction ?? 'download';
  const downloadUrl = globalThis.hubleto.config.projectUrl + '/' + props.urlSlug + '/' + form.id + '/' + downloadAction;
  return <a className='btn btn-primary' href={downloadUrl} target='_blank'>
    <span className='icon'><i className='fas fa-download'></i></span>
    <span className='text'>{props.text}</span>
  </a>;
}

export default FileDownloadButton;
