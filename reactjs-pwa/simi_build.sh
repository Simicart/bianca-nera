cd ./packages/siminia
#git reset --hard
#git pull origin customize/2980-basmajiramez@gmail.com-Bianca-Nera
#git checkout customize/2980-basmajiramez@gmail.com-Bianca-Nera
#git pull
yarn install
yarn run build
cd ../../
#cp -f .env-file ./packages/siminia/.env
yarn install
yarn run build

rm -rf ./static/*
cp -rpf ./packages/siminia/dist/ ./static/
cp -rpf ./packages/siminia/static/ ./static/
chmod -Rf 777 ./packages/siminia/dist/

pm2 delete biancanera
NODE_ENV=production PORT=1402 pm2 start --name biancanera  npm -- run stage:siminia
curl -v https://bianca-nera.com/Store/simiconnector/index/updatepwaversion
