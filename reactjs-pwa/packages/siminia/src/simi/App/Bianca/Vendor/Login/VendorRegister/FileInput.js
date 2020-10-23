import React from 'react';
import { asField } from 'informed';
import { FieldIcons, Message } from 'src/components/Field';
import { uploadFile } from 'src/simi/Model/Clothing';
import { showFogLoading, hideFogLoading } from 'src/simi/BaseComponents/Loading/GlobalLoading';

const FileInput = asField(({ fieldState, fieldApi, ...props }) => {
    const { value } = fieldState;
    const { setValue, setTouched } = fieldApi;
    const { field, onChange, onBlur, initialValue, forwardedRef, message, before, after, ...rest } = props;

    const getBase64 = (file, cb) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = function () {
            cb(reader.result)
        };
        reader.onerror = function (error) {
            console.log('Error: ', error);
        };
    }

    return (
        <React.Fragment>
            <FieldIcons after={after} before={before}>
                <input type="hidden" name={field} ref={forwardedRef} value={value || initialValue || ''} />
                <input {...rest} type="file" style={{lineHeight: '48px'}}
                    onChange={e => {
                        if (onChange) {
                            onChange(e);
                        }
                        // Upload file temp
                        const filePath = e.target.files[0];
                        console.log(filePath)
                        if (filePath) {
                            getBase64(filePath, (result) => {
                                if (result) {
                                    showFogLoading();
                                    let base64 = result.split("base64,");
                                    try{
                                        base64 = base64[base64.length-1].split('"')[0];
                                    }catch(e){console.warn(e)}
                                    uploadFile((data) => {
                                        console.log(data)
                                        // console.log(data.uploadfile && data.uploadfile.full_path || '')
                                        forwardedRef.current.value = data.uploadfile && data.uploadfile.full_path || '';
                                        setValue(forwardedRef.current.value);
                                        setTouched(true);
                                        hideFogLoading();
                                    }, {
                                            fileData: {
                                                type: filePath.type,
                                                name: filePath.name,
                                                size: filePath.size,
                                                base64: base64
                                            }
                                        }
                                    );
                                    return;
                                }
                                showToastMessage(Identify.__('Cannot read file content'))
                                hideFogLoading();
                            });
                        } else {
                            console.log('cancel select')
                            setValue('');
                            forwardedRef.current.value = '';
                            console.log(forwardedRef.current.value)
                            setTouched(true);
                        }
                    }}
                    onBlur={e => {
                        if (onBlur) {
                            onBlur(e);
                        }
                    }}
                />
            </FieldIcons>
            <Message fieldState={fieldState}>{message}</Message>
        </React.Fragment>
    );
});

export default FileInput;