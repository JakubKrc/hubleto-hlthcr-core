import React from 'react';
import ImageUploading, { ImageType } from 'react-images-uploading';
import Translator from '@hubleto/react-ui/core/Translator';
import Input, { type InputProps, InputMetaContext } from '@hubleto/react-ui/components/fc/Input';
import { FormMetaContext } from '@hubleto/react-ui/components/fc/Form';

const T = new Translator('Hubleto/App/External/WaiBlue/HlthcrCore/Loader', 'Components/ProtectedFileInput');

interface ProtectedFileInputProps extends InputProps {
  // e.g. 'hlthcr-medical-exams/exams/{%ID%}/scan'; set by the StoredFile column (HlthcrCore/Db/Column/StoredFile).
  downloadUrl?: string,
}

/**
 * File input whose file name links to a checked download route. Hubleto's file input links to the upload
 * folder, which is not public for health data (it answers 403).
 */
const ProtectedFileValue = (props: ProtectedFileInputProps) => {
  const input = React.useContext(InputMetaContext);
  const form = React.useContext(FormMetaContext);
  if (!input.value) return <></>;

  // A file chosen in this form is not saved yet, so it has no download link.
  const isNewUpload = typeof input.value === 'object';
  if (isNewUpload) return <span className='text'>{input.value.fileName}</span>;

  const fileName = String(input.value).split('/').pop();
  const downloadPath = (props.downloadUrl ?? '').replace('{%ID%}', String(form.id));
  return <a
    href={globalThis.hubleto.config.projectUrl + '/' + downloadPath}
    target='_blank'
    onClick={(event) => { event.stopPropagation(); }}
    className='btn btn-primary-outline btn-small'
  >
    <span className='text'>{fileName}</span>
  </a>;
}

const ProtectedFileUpload = (props: ProtectedFileInputProps) => {
  const input = React.useContext(InputMetaContext);

  function changeFile(files: Array<ImageType>) {
    const file: any = files[0];
    input.changeValue({
      fileName: file ? file.file.name : null,
      fileData: file ? file.fileData : null,
      fileSize: file ? (parseInt(file.file.size) ?? 0) : null,
    });
  }

  return <>
    <ProtectedFileValue {...props} />
    {input.readonly ? null : <ImageUploading
      value={input.value && input.value.fileData != null ? [input.value] : []}
      onChange={changeFile}
      maxNumber={1}
      dataURLKey='fileData'
      allowNonImageType={true}
    >
      {({ onImageUpload, isDragging, dragProps }) => <div className='upload__image-wrapper'>
        <button
          className='btn btn-small btn-transparent'
          style={isDragging ? { color: 'red' } : undefined}
          onClick={onImageUpload}
          {...dragProps}
        >
          <span className='icon'><i className='fas fa-cloud-arrow-up'></i></span>
          <span className='text'>{T.translate('Upload file')}</span>
        </button>
      </div>}
    </ImageUploading>}
  </>;
}

const ProtectedFileInput = (props: ProtectedFileInputProps) => <Input
  inputClassName='varchar'
  isInitialized={true}
  renderValueComponent={() => <ProtectedFileValue {...props} />}
  renderInputComponent={() => <ProtectedFileUpload {...props} />}
  {...props}
/>;

export default ProtectedFileInput;
